<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拖放功能改进：允许放回原位</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            color: #fff;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            text-align: center;
        }
        
        h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.85);
        }
        
        .instructions {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 20px;
            margin: 20px auto 40px;
            max-width: 800px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .instructions h2 {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .instructions ul {
            text-align: left;
            margin: 15px 0;
            padding-left: 25px;
        }
        
        .instructions li {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .grid-item {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: grab;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(4px);
            border: 2px solid rgba(255, 255, 255, 0.18);
            position: relative;
            overflow: hidden;
            min-height: 180px;
        }
        
        .grid-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.3);
        }
        
        .grid-item.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }
        
        .grid-item.drop-target {
            background: rgba(46, 204, 113, 0.3);
            border: 2px dashed #2ecc71;
            animation: pulse 1.5s infinite;
        }
        
        .grid-item.original-pos {
            background: rgba(155, 89, 182, 0.3);
            border: 2px dashed #9b59b6;
        }
        
        .icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }
        
        .item-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .item-desc {
            font-size: 0.95rem;
            opacity: 0.9;
            line-height: 1.5;
        }
        
        .status {
            margin-top: 30px;
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            font-size: 1.1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .highlight {
            background: linear-gradient(90deg, #ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: bold;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.5); }
            70% { box-shadow: 0 0 0 12px rgba(46, 204, 113, 0); }
            100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
        }
        
        .footer {
            margin-top: 40px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 15px;
            }
            
            h1 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>拖放功能改进</h1>
        <p class="subtitle">现在可以将元素拖回原始位置</p>
        
        <div class="instructions">
            <h2>使用说明</h2>
            <ul>
                <li>拖拽任意卡片到其他位置进行交换</li>
                <li><span class="highlight">新功能</span>：可以将卡片拖回原始位置（紫色边框）</li>
                <li>绿色边框表示有效的放置区域</li>
                <li>尝试将元素拖回原位体验改进效果</li>
            </ul>
            <p>状态：<span id="status">准备拖放...</span></p>
        </div>
        
        <div class="grid-container" id="gridContainer">
            <div class="grid-item" draggable="true" data-id="1">
                <div class="icon">📁</div>
                <div class="item-title">文档管理</div>
                <div class="item-desc">组织你的文件和文件夹</div>
            </div>
            <div class="grid-item" draggable="true" data-id="2">
                <div class="icon">📊</div>
                <div class="item-title">数据分析</div>
                <div class="item-desc">可视化你的业务数据</div>
            </div>
            <div class="grid-item" draggable="true" data-id="3">
                <div class="icon">🔒</div>
                <div class="item-title">安全设置</div>
                <div class="item-desc">保护你的账户安全</div>
            </div>
            <div class="grid-item" draggable="true" data-id="4">
                <div class="icon">🔄</div>
                <div class="item-title">同步工具</div>
                <div class="item-desc">跨设备同步你的数据</div>
            </div>
            <div class="grid-item" draggable="true" data-id="5">
                <div class="icon">📱</div>
                <div class="item-title">移动应用</div>
                <div class="item-desc">随时随地访问你的内容</div>
            </div>
            <div class="grid-item" draggable="true" data-id="6">
                <div class="icon">🌐</div>
                <div class="item-title">网络设置</div>
                <div class="item-desc">配置你的网络连接</div>
            </div>
        </div>
        
        <div class="status">
            <p><strong>问题解决：</strong>原始位置现在可以接受拖放操作，用户可以将元素拖回其原始位置，解决了之前无法放回原位的问题。</p>
        </div>
        
        <div class="footer">
            HTML5 拖放功能改进 | 解决方案演示
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gridItems = document.querySelectorAll('.grid-item');
            const statusElement = document.getElementById('status');
            let draggedItem = null;
            let originalPosition = null;
            
            // 初始化拖放事件
            gridItems.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragend', handleDragEnd);
                item.addEventListener('dragover', handleDragOver);
                item.addEventListener('dragenter', handleDragEnter);
                item.addEventListener('dragleave', handleDragLeave);
                item.addEventListener('drop', handleDrop);
            });
            
            function handleDragStart(e) {
                draggedItem = this;
                originalPosition = this;
                
                // 添加拖动样式
                this.classList.add('dragging');
                
                // 标记原始位置
                this.classList.add('original-pos');
                
                // 设置数据
                e.dataTransfer.setData('text/plain', this.getAttribute('data-id'));
                
                // 设置拖放效果
                e.dataTransfer.effectAllowed = 'move';
                
                statusElement.textContent = `开始拖动: ${this.querySelector('.item-title').textContent}`;
            }
            
            function handleDragEnd(e) {
                // 移除所有样式
                gridItems.forEach(item => {
                    item.classList.remove('dragging', 'drop-target', 'original-pos');
                });
                
                statusElement.textContent = '拖放完成!';
                draggedItem = null;
                originalPosition = null;
            }
            
            function handleDragOver(e) {
                e.preventDefault();
                return false;
            }
            
            function handleDragEnter(e) {
                // 防止触发原始位置的dragenter
                if (this === originalPosition) return;
                
                this.classList.add('drop-target');
                statusElement.textContent = `可以放置到: ${this.querySelector('.item-title').textContent}`;
            }
            
            function handleDragLeave(e) {
                // 防止触发原始位置的dragleave
                if (this === originalPosition) return;
                
                this.classList.remove('drop-target');
                statusElement.textContent = '拖动中...';
            }
            
            function handleDrop(e) {
                e.preventDefault();
                
                // 移除所有目标样式
                gridItems.forEach(item => {
                    item.classList.remove('drop-target');
                });
                
                // 检查是否放置回原始位置
                if (this === originalPosition) {
                    statusElement.textContent = `已将元素放回原始位置: ${this.querySelector('.item-title').textContent}`;
                    this.classList.remove('original-pos');
                    return;
                }
                
                // 交换元素（如果放置到其他位置）
                const draggedId = e.dataTransfer.getData('text/plain');
                const targetId = this.getAttribute('data-id');
                
                // 交换标题和描述
                const draggedTitle = draggedItem.querySelector('.item-title').textContent;
                const targetTitle = this.querySelector('.item-title').textContent;
                
                draggedItem.querySelector('.item-title').textContent = targetTitle;
                this.querySelector('.item-title').textContent = draggedTitle;
                
                // 交换描述
                const draggedDesc = draggedItem.querySelector('.item-desc').textContent;
                const targetDesc = this.querySelector('.item-desc').textContent;
                
                draggedItem.querySelector('.item-desc').textContent = targetDesc;
                this.querySelector('.item-desc').textContent = draggedDesc;
                
                // 交换图标
                const draggedIcon = draggedItem.querySelector('.icon').textContent;
                const targetIcon = this.querySelector('.icon').textContent;
                
                draggedItem.querySelector('.icon').textContent = targetIcon;
                this.querySelector('.icon').textContent = draggedIcon;
                
                // 交换数据ID
                draggedItem.setAttribute('data-id', targetId);
                this.setAttribute('data-id', draggedId);
                
                statusElement.textContent = `已交换: ${draggedTitle} 与 ${targetTitle}`;
                
                return false;
            }
        });
    </script>
</body>
</html>