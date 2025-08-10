<?php
// editTimetable.php
// Place under pages/ or wherever. Adjust require path to your dbconnect.

require_once __DIR__ . "/../../dbconnect.php"; // adjust relative path if needed

// Helper: map timeSlot (1..16) -> '09:00' etc (30-min steps)
function timeslot_to_time($slot) {
    $slot = intval($slot);
    $start_minutes = (8 * 60) + ($slot * 30); // if slot1 -> 09:00 (8*60 + 30)
    $h = intdiv($start_minutes, 60);
    $m = $start_minutes % 60;
    return sprintf("%02d:%02d:00", $h, $m);
}

// Load auxiliary name maps
$subjects = [];
$venues = [];
$lecturers = [];

// load subject fullname
$res = $conn->query("SELECT ID, fullname FROM subject");
while ($r = $res->fetch_assoc()) { $subjects[$r['ID']] = $r['fullname']; }

// load venues
$res = $conn->query("SELECT ID, Name FROM venue");
while ($r = $res->fetch_assoc()) { $venues[$r['ID']] = $r['Name']; }

// load lecturers
$res = $conn->query("SELECT ID, name FROM lecturer");
while ($r = $res->fetch_assoc()) { $lecturers[$r['ID']] = $r['name']; }

// Two entry modes:
// 1) generated JSON posted from generator: $_POST['generated_json'] (stringified array of genes)
//    expected shape: an array of gene objects like the example you showed
// 2) or timetable_id provided in GET: ?timetable_id=T0001 (load DB records)

$mode = '';
$calendars = []; // array of calendars to render: each is ['id'=>..., 'title'=>..., 'events'=>[...], 'batchId'=>...]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['generated_json'])) {
    $mode = 'generated';
    $json = $_POST['generated_json'];
    $genes = json_decode($json, true);
    if (!is_array($genes)) $genes = [];

    // Group by batchId -> build events per batch
    $by_batch = [];
    foreach ($genes as $g) {
        $batch = $g['batchId'] ?? 'B0001';
        $by_batch[$batch][] = $g;
    }
    foreach ($by_batch as $batchId => $list) {
        $events = [];
        foreach ($list as $g) {
            $start = timeslot_to_time($g['timeSlot']);
            $duration_slots = intval($g['duration'] ?? 1);
            $end_slot = intval($g['timeSlot']) + $duration_slots;
            $end = timeslot_to_time($end_slot);

            $title = ($subjects[$g['subjectId']] ?? $g['subjectId'])
                   . " · " . ($venues[$g['venueId']] ?? $g['venueId'])
                   . " · " . ($lecturers[$g['lecturerId']] ?? $g['lecturerId']);

            // FullCalendar expects ISO date times. We'll use an arbitrary week start date and weekday mapping.
            // Map 'MO','TU','WE','TH','FR' to dates of a template week, e.g. 2025-08-11 (Monday).
            $day_map = ['MO'=>'2025-08-11','TU'=>'2025-08-12','WE'=>'2025-08-13','TH'=>'2025-08-14','FR'=>'2025-08-15'];
            $day = $g['day'];
            $date = $day_map[$day] ?? $day_map['MO'];

            $events[] = [
                'id' => $g['UUID'],
                'title' => $title,
                'start' => $date . "T" . substr($start,0,8),
                'end' => $date . "T" . substr($end,0,8),
                'extendedProps' => [
                    'batchId' => $g['batchId'],
                    'subjectId' => $g['subjectId'],
                    'venueId' => $g['venueId'],
                    'lecturerId' => $g['lecturerId'],
                    'duration_slots' => $duration_slots
                ]
            ];
        }
        $calendars[] = [
            'id' => $batchId,
            'title' => "Generated - $batchId",
            'events' => $events,
            'batchId' => $batchId
        ];
    }
} elseif (!empty($_GET['timetable_id'])) {
    $mode = 'db';
    $timetable_id = $conn->real_escape_string($_GET['timetable_id']);

    // fetch active timetable check
    $sql = "SELECT * FROM timetable WHERE ID = '" . $timetable_id . "' LIMIT 1";
    $res = $conn->query($sql);
    $timetable = $res->fetch_assoc();

    // fetch slots for that timetable (Active not needed because slots link to timetable id)
    $sql = "SELECT * FROM timetableslot WHERE timetable_id = '" . $timetable_id . "'";
    $res = $conn->query($sql);
    $events = [];
    while ($g = $res->fetch_assoc()) {
        $start = timeslot_to_time($g['timeSlot']);
        $end_slot = intval($g['timeSlot']) + intval($g['duration']);
        $end = timeslot_to_time($end_slot);
        $day_map = ['MO'=>'2025-08-11','TU'=>'2025-08-12','WE'=>'2025-08-13','TH'=>'2025-08-14','FR'=>'2025-08-15'];
        $date = $day_map[$g['day']] ?? $day_map['MO'];

        $title = ($subjects[$g['subject_id']] ?? $g['subject_id'])
               . " · " . ($venues[$g['venue_id']] ?? $g['venue_id'])
               . " · " . ($lecturers[$g['lecturer_id']] ?? $g['lecturer_id']);

        $events[] = [
            'id' => 'db-' . $g['id'],
            'db_id' => $g['id'],
            'title' => $title,
            'start' => $date . "T" . substr($start,0,8),
            'end' => $date . "T" . substr($end,0,8),
            'extendedProps' => [
                'batchId' => $g['batch_id'],
                'subjectId' => $g['subject_id'],
                'venueId' => $g['venue_id'],
                'lecturerId' => $g['lecturer_id'],
                'duration_slots' => intval($g['duration'])
            ]
        ];
    }
    $calendars[] = [
        'id' => $timetable_id,
        'title' => "Timetable " . ($timetable_id),
        'events' => $events,
        'timetable_id' => $timetable_id
    ];
} else {
    // neither: show selection hint
    $mode = 'none';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Edit Timetable</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>
  <style>
    /* basic layout: left calendars column, right sidebar */
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background:#f6f8fa; }
    .header { padding: 16px 24px; background: #fff; border-bottom:1px solid #e6edf3; display:flex; justify-content:space-between; align-items:center;}
    .page-wrap { display:flex; gap:20px; padding: 20px; max-width:1200px; margin: 0 auto; }
    .left { flex: 1; display:flex; flex-direction:column; gap:20px; }
    .calendar-card { background:#fff; padding:12px; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,0.04); }
    .calendar-title { font-weight:600; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }
    .right { width:320px; }
    .external-events { background:#fff; padding:12px; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,0.04); min-height:200px;}
    .external-event { padding:8px 10px; margin:6px 0; background:#eef2ff; border-radius:6px; cursor:grab; }
    .save-area { background:#fff; padding:12px; border-radius:10px; margin-top:12px; box-shadow:0 6px 20px rgba(0,0,0,0.04); }
    .btn { padding:10px 16px; border-radius:6px; border:none; cursor:pointer; font-weight:600; }
    .btn-save { background:#2f80ed; color:#fff; }
    .btn-cancel { background:#f5f7fa; color:#333; margin-left:10px; }
    .unsaved { color:#d97706; font-weight:600; }
  </style>
</head>
<body>
  <div class="header">
    <div><strong>Edit Timetable</strong></div>
    <div>
      <button onclick="window.location.href='./dashboardTimetable.php'" class="btn btn-cancel">Back</button>
    </div>
  </div>

  <div class="page-wrap">
    <div class="left" id="leftCalendars">
      <?php if ($mode === 'none'): ?>
        <div class="calendar-card">No timetable data. Please open from dashboard or POST generated JSON.</div>
      <?php else: ?>
        <!-- calendars will be rendered here by JS -->
      <?php endif; ?>
    </div>

    <div class="right">
      <div class="external-events">
        <h4>Temporary Slots</h4>
        <div id="externalList">
          <!-- optionally prefill with events that are not placed -->
        </div>
        <hr>
        <div>
          <label>New External (subjectId):</label><br>
          <input id="new_subject" placeholder="SB0001">
          <input id="new_venue" placeholder="V0001">
          <input id="new_lecturer" placeholder="L0002">
          <button id="addExternal" style="margin-top:8px;" class="btn">Add</button>
        </div>
      </div>

      <div class="save-area">
        <div>Changes: <span id="unsavedFlag">0</span> <span class="unsaved">unsaved</span></div>
        <div style="margin-top:10px;">
          <button id="saveAll" class="btn btn-save">Save to DB</button>
          <button id="resetBtn" class="btn btn-cancel">Reset (reload)</button>
        </div>
      </div>
      <div style="margin-top:12px; font-size:13px; color:#666;">
        Notes: drag events to different day/time. Events represent subject · venue · lecturer.
      </div>
    </div>
  </div>

<script>
/*
  Client-side:
  - build one calendar per entry in PHP $calendars
  - support dragging external events into calendars, resizing, moving
  - track unsaved changes, warn on unload
  - Save: collect events per calendar -> POST to save_timetable.php
*/

// PHP-pass calendars to JS
const calendarsData = <?php echo json_encode($calendars, JSON_UNESCAPED_UNICODE); ?>;
let unsavedCount = 0;
const unsavedFlag = document.getElementById('unsavedFlag');

function markUnsaved(delta=1){
  unsavedCount = Math.max(0, unsavedCount + delta);
  unsavedFlag.textContent = unsavedCount;
}
window.addEventListener('beforeunload', function(e){
  if (unsavedCount > 0) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure to leave?';
  }
});

// Helper to make an element draggable for FullCalendar external drag
function makeExternal(el, meta) {
  el.draggable = true;
  el.classList.add('external-event');
  el.dataset.meta = JSON.stringify(meta);
  el.addEventListener('dragstart', function(ev){
    ev.dataTransfer.setData('text/plain', JSON.stringify(meta));
  });
}

// create initial external list (empty)
const externalList = document.getElementById('externalList');

// add external adder
document.getElementById('addExternal').addEventListener('click', () => {
  const s = document.getElementById('new_subject').value.trim();
  const v = document.getElementById('new_venue').value.trim();
  const l = document.getElementById('new_lecturer').value.trim();
  if (!s) return alert('Enter subject id');
  const title = `${s} · ${v || ''} · ${l || ''}`;
  const div = document.createElement('div');
  const meta = { subjectId: s, venueId: v, lecturerId: l, duration_slots: 2 };
  div.textContent = title;
  makeExternal(div, meta);
  externalList.appendChild(div);
});

// render calendars (one by one)
const left = document.getElementById('leftCalendars');
const calendarObjs = {}; // id -> FullCalendar instance

calendarsData.forEach((cal, idx) => {
  // create card wrapper
  const card = document.createElement('div');
  card.className = 'calendar-card';
  const titleRow = document.createElement('div');
  titleRow.className = 'calendar-title';
  titleRow.innerHTML = `<div>${cal.title}</div><div><small>${cal.batchId || cal.timetable_id || ''}</small></div>`;
  card.appendChild(titleRow);

  const el = document.createElement('div');
  el.id = 'calendar-' + idx;
  el.style.minHeight = '450px';
  card.appendChild(el);
  left.appendChild(card);

  // prepare events
  const events = cal.events || [];

  // initialize FullCalendar
  const calendar = new FullCalendar.Calendar(el, {
    initialView: 'timeGridWeek',
    firstDay: 1, // Monday
    slotDuration: '00:30:00',
    allDaySlot: false,
    expandRows: true,
    headerToolbar: {
      left: 'title',
      center: '',
      right: 'today prev,next'
    },
    initialDate: '2025-08-11', // template week Monday
    slotMinTime: '08:30:00',
    slotMaxTime: '18:30:00',
    editable: true,
    droppable: true,
    selectable: true,
    events: events,
    eventReceive: function(info) {
      // info.draggedEl has meta
      const metaRaw = info.draggedEl.dataset.meta;
      if (metaRaw) {
        const meta = JSON.parse(metaRaw);
        // adjust title to nicer text if meta has IDs (server side names are better, but we can show ids)
        info.event.setProp('title', (meta.subjectId || '') + ' · ' + (meta.venueId||'') + ' · ' + (meta.lecturerId||''));
        info.event.setExtendedProp('subjectId', meta.subjectId);
        info.event.setExtendedProp('venueId', meta.venueId);
        info.event.setExtendedProp('lecturerId', meta.lecturerId);
        info.event.setExtendedProp('duration_slots', meta.duration_slots || 1);
      }
      markUnsaved(1);
    },
    eventDrop: function(info){ markUnsaved(1); },
    eventResize: function(info){ markUnsaved(1); },
    eventChange: function(info){ markUnsaved(1); }
  });
  calendar.render();
  calendarObjs[cal.id || cal.title || idx] = calendar;
});

// Preload some external events from calendars that have events but are not placed? 
// (In our mode, all generated events are loaded into calendars, so external list may be empty.)

// Save routine
document.getElementById('saveAll').addEventListener('click', async function(){
  const payload = [];
  // For generated mode, we may need to create new timetable(s) per calendar -> server will handle
  calendarsData.forEach(cal => {
    const calId = cal.id;
    const calendar = calendarObjs[calId] || calendarObjs[cal.title];
    if (!calendar) return;
    const events = calendar.getEvents();
    const evlist = events.map(ev => {
      // convert event start back to day and timeslot
      const start = ev.start; // Date object
      const dayMap = {'1':'MO','2':'TU','3':'WE','4':'TH','5':'FR'};
      const dow = start.getDay(); // 1=Mon
      const day = dayMap[String(dow)] || 'MO';
      // compute timeslot index: assuming 09:00 -> slot1 mapping as earlier
      const hours = start.getHours();
      const minutes = start.getMinutes();
      // slot calculation: slot1 = 09:00 -> 9:00 -> (9*60 - 8*60)/30 = 2? To keep consistent with server, we'll use inverse of timeslot_to_time logic
      // Our server timeslot_to_time used (8*60 + slot*30) as start_min => slot = (start_min - 8*60)/30
      const start_min = hours*60 + minutes;
      const slot = Math.round((start_min - (8*60)) / 30);
      const durationSlots = ev.extendedProps.duration_slots || Math.max(1, Math.round(( (ev.end - ev.start) / (1000*60) )/30 ));
      return {
        uuid: ev.id,
        batchId: cal.batchId || null,
        timetable_id: cal.timetable_id || null,
        subjectId: ev.extendedProps.subjectId || null,
        venueId: ev.extendedProps.venueId || null,
        lecturerId: ev.extendedProps.lecturerId || null,
        day: day,
        timeSlot: slot,
        duration: durationSlots
      };
    });
    payload.push({ calendar: calId, batchId: cal.batchId || null, timetable_id: cal.timetable_id || null, events: evlist });
  });

  // POST JSON
  const res = await fetch('saveTimetable.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ payload })
  });
  const data = await res.json();
  if (data.status === 'success') {
    alert('Saved successfully');
    unsavedCount = 0;
    unsavedFlag.textContent = '0';
    // optionally reload to reflect db IDs
    location.reload();
  } else {
    alert('Save failed: ' + (data.message || 'unknown'));
  }
});

// Reset button
document.getElementById('resetBtn').addEventListener('click', () => location.reload());

</script>
</body>
</html>
