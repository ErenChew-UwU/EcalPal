// 时间表状态管理
let currentTimetable = null;

// 初始化拖拽功能
function initDragAndDrop() {
    // 使所有课程可拖拽
    document.querySelectorAll('.timetable-slot').forEach(slot => {
        slot.draggable = true;
        
        slot.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', slot.dataset.slotId);
            slot.classList.add('dragging');
        });
        
        slot.addEventListener('dragend', () => {
            slot.classList.remove('dragging');
        });
    });
    
    // 设置时间格子接受拖拽
    document.querySelectorAll('.timetable-cell').forEach(cell => {
        cell.addEventListener('dragover', (e) => {
            e.preventDefault();
            cell.classList.add('drop-target');
        });
        
        cell.addEventListener('dragleave', () => {
            cell.classList.remove('drop-target');
        });
        
        cell.addEventListener('drop', (e) => {
            e.preventDefault();
            cell.classList.remove('drop-target');
            
            const slotId = e.dataTransfer.getData('text/plain');
            const slotElement = document.querySelector(`[data-slot-id="${slotId}"]`);
            
            if (slotElement) {
                moveSlotToCell(slotElement, cell);
            }
        });
    });
}

// 移动课程到新位置
function moveSlotToCell(slotElement, cell) {
    const slotId = slotElement.dataset.slotId;
    const newDay = cell.dataset.day;
    const newTime = parseInt(cell.dataset.time);
    
    // 在数据中更新位置
    const slot = currentTimetable.slots.find(s => s.id === slotId);
    if (!slot) return;
    
    // 保存旧位置用于回滚
    const oldPosition = {
        day: slot.day,
        start_time: slot.start_time,
        element: slotElement.cloneNode(true)
    };
    
    // 检查冲突
    if (hasConflict(slot, newDay, newTime)) {
        showConflictWarning(slot, newDay, newTime);
        return;
    }
    
    // 更新数据
    slot.day = newDay;
    slot.start_time = newTime;
    
    // 更新UI
    cell.appendChild(slotElement);
    slotElement.dataset.day = newDay;
    slotElement.dataset.time = newTime;
    
    // 添加撤销按钮
    addUndoButton(slotElement, oldPosition);
}

// 冲突检测函数
function hasConflict(slot, newDay, newTime) {
    const slotEndTime = newTime + slot.duration;
    
    // 检查同一时间、同一地点是否有其他课程
    const hasVenueConflict = currentTimetable.slots.some(otherSlot => {
        return otherSlot.id !== slot.id &&
               otherSlot.venue_id === slot.venue_id &&
               otherSlot.day === newDay &&
               otherSlot.start_time < slotEndTime &&
               (otherSlot.start_time + otherSlot.duration) > newTime;
    });
    
    // 检查同一时间、同一教师是否有其他课程
    const hasTeacherConflict = currentTimetable.slots.some(otherSlot => {
        return otherSlot.id !== slot.id &&
               otherSlot.lecturer_id === slot.lecturer_id &&
               otherSlot.day === newDay &&
               otherSlot.start_time < slotEndTime &&
               (otherSlot.start_time + otherSlot.duration) > newTime;
    });
    
    return hasVenueConflict || hasTeacherConflict;
}

// 显示冲突警告
function showConflictWarning(slot, day, time) {
    // 实现冲突提示UI
    alert(`无法移动: ${slot.subject_name} 到 ${day} ${time}:00\n原因: 时间/地点冲突`);
}

// 添加撤销按钮
function addUndoButton(slotElement, oldPosition) {
    const undoBtn = document.createElement('button');
    undoBtn.className = 'undo-btn';
    undoBtn.innerHTML = '↺';
    undoBtn.title = '撤销移动';
    
    undoBtn.addEventListener('click', () => {
        // 恢复原始位置
        const originalCell = document.querySelector(
            `.timetable-cell[data-day="${oldPosition.day}"][data-time="${oldPosition.start_time}"]`
        );
        
        if (originalCell) {
            slotElement.remove();
            originalCell.appendChild(oldPosition.element);
            
            // 恢复数据
            const slot = currentTimetable.slots.find(s => s.id === slotElement.dataset.slotId);
            if (slot) {
                slot.day = oldPosition.day;
                slot.start_time = oldPosition.start_time;
            }
        }
    });
    
    slotElement.appendChild(undoBtn);
}

// 保存时间表
function saveTimetable() {
    if (!currentTimetable) return;
    
    fetch('./generate_timetable/save_timetable.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            timetable: currentTimetable
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(`时间表保存成功! ID: ${data.timetable_id}`);
            // 重置状态
            currentTimetable = null;
            document.getElementById('save-btn').disabled = true;
        } else {
            alert('保存失败: ' + data.message);
        }
    });
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
    // 生成按钮
    document.getElementById('generate-btn').addEventListener('click', () => {
        const batchId = document.getElementById('batch-select').value;
        generateTimetable(batchId);
    });
    
    // 保存按钮
    document.getElementById('save-btn').addEventListener('click', saveTimetable);
});

// 生成时间表
function generateTimetable(batchId) {
    fetch(`./generate_timetable/generateTimetable.php?batch_id=${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                currentTimetable = data.timetable;
                renderTimetable(currentTimetable);
                initDragAndDrop();
                document.getElementById('save-btn').disabled = false;
            } else {
                alert('生成失败: ' + (data.message || '未知错误'));
            }
        })
        .catch(error => {
            console.error('生成错误:', error);
            alert('生成过程中出错');
        });
}

// 渲染时间表
function renderTimetable(timetable) {
    const container = document.getElementById('timetable-container');
    container.innerHTML = '';
    
    // 创建时间表结构
    const days = ['MO', 'TU', 'WE', 'TH', 'FR'];
    const times = Array.from({length: 10}, (_, i) => i + 1); // 1-10时间段
    
    // 创建表头
    const table = document.createElement('table');
    table.className = 'timetable';
    
    // 添加时间轴
    const headerRow = document.createElement('tr');
    headerRow.appendChild(document.createElement('th')); // 左上角空白
    
    days.forEach(day => {
        const th = document.createElement('th');
        th.textContent = getDayName(day);
        headerRow.appendChild(th);
    });
    
    table.appendChild(headerRow);
    
    // 添加时间行
    times.forEach(time => {
        const row = document.createElement('tr');
        
        // 时间标签
        const timeCell = document.createElement('td');
        timeCell.className = 'time-label';
        timeCell.textContent = `${time}:00`;
        row.appendChild(timeCell);
        
        // 每天的时间格子
        days.forEach(day => {
            const cell = document.createElement('td');
            cell.className = 'timetable-cell';
            cell.dataset.day = day;
            cell.dataset.time = time;
            
            // 查找这个时间段的课程
            const slot = timetable.slots.find(s => 
                s.day === day && s.start_time === time
            );
            
            if (slot) {
                const slotElement = createSlotElement(slot);
                cell.appendChild(slotElement);
            }
            
            row.appendChild(cell);
        });
        
        table.appendChild(row);
    });
    
    container.appendChild(table);
}

// 创建课程元素
function createSlotElement(slot) {
    const element = document.createElement('div');
    element.className = 'timetable-slot';
    element.dataset.slotId = slot.id;
    element.dataset.day = slot.day;
    element.dataset.time = slot.start_time;
    
    element.innerHTML = `
        <div class="subject">${slot.subject_id}</div>
        <div class="lecturer">讲师: ${slot.lecturer_id}</div>
        <div class="venue">地点: ${slot.venue_id}</div>
        <div class="duration">时长: ${slot.duration}小时</div>
    `;
    
    return element;
}

// 辅助函数：获取完整日期名称
function getDayName(shortDay) {
    const days = {
        'MO': '星期一',
        'TU': '星期二',
        'WE': '星期三',
        'TH': '星期四',
        'FR': '星期五'
    };
    return days[shortDay] || shortDay;
}