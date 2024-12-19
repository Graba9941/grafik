<?php
require 'lib/functions.php';

if(!isLoggedIn()){
    header('Location: index.php');
    exit;
}

$settings = getSettings();
$workHours = getWorkHours();
$userId = $_SESSION['user_id'];
$error = '';
$success = '';
$editEvent = null;
$modal = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $type = isset($_POST['type']) ? htmlspecialchars(trim($_POST['type'])) : '';
    $start = isset($_POST['start']) ? htmlspecialchars(trim($_POST['start'])) : '';
    $end = isset($_POST['end']) ? htmlspecialchars(trim($_POST['end'])) : '';
     $id = isset($_POST['id']) ? htmlspecialchars(trim($_POST['id'])) : null;
    
    if(validateDateTime($start) && validateDateTime($end)){
        if($start >= $end){
            $error = "Data startu musi być wcześniejsza niż data końca.";
        } else {
            $eventData = array(
                'userId' => $userId,
                'type' => $type,
                'start' => $start,
                'end' => $end
            );
            if($id) {
                updateEvent($id, $eventData);
                $success = "Zdarzenie zostało zaktualizowane.";
            } else {
                addEvent($eventData);
                $success = "Zdarzenie zostało dodane.";
            }
            $modal = false;
        }
    } else {
        $error = "Nieprawidłowy format daty.";
    }
}

if(isset($_GET['delete'])){
   $delete_id = isset($_GET['delete']) ? htmlspecialchars(trim($_GET['delete'])) : '';
    deleteEvent($delete_id);
    $success = "Zdarzenie zostało usunięte.";
}

if(isset($_GET['edit'])){
    $modal = true;
    $edit_id = isset($_GET['edit']) ? htmlspecialchars(trim($_GET['edit'])) : '';
    $eventId = $edit_id;
    $events = getSchedule();
    foreach($events as $event){
        if($event['id'] === $eventId && $event['userId'] === $userId){
            $editEvent = $event;
            break;
        }
    }
}

$events = getSchedule();
$myEvents = array();
foreach($events as $event){
    if($event['userId'] === $userId){
         $color = '';
        if(isset($settings['eventTypes'][$event['type']])){
            $color = $settings['eventTypes'][$event['type']]['color'];
        }
         $myEvents[] = array(
            'id' => $event['id'],
             'title' => $settings['eventTypes'][$event['type']]['short'],
            'start' => $event['start'],
            'end' => $event['end'],
            'color' => $color
        );
    }
}

require 'components/header.php';

?>
<h2 class="text-center">
         <i class="fas fa-calendar-alt"></i> Mój Grafik
      </h2>
<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
    <div id="calendar" class="mb-4"></div>
    
    <div class="row">
        <div class="col-12">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addEventModal">
     <i class="fas fa-plus"></i> Dodaj Wydarzenie
</button>
       </div>
   </div>

   <div class="row">
        <div class="col-12">
        <h3>
   <i class="fas fa-list"></i> Moje Zdarzenia
 </h3>
             <div class="table-responsive">
                <table class="table table-striped">
                   <thead>
                       <tr>
                           <th>Typ</th>
                           <th>Start</th>
                            <th>Koniec</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(count($myEvents) > 0): ?>
                         <?php foreach($events as $event):
                         if($event['userId'] === $userId):?>
                            <tr>
                                <td><?php echo htmlspecialchars($settings['eventTypes'][$event['type']]['name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($event['start'])); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($event['end'])); ?></td>
                                 <td>
                                     <button data-id="<?php echo htmlspecialchars($event['id']); ?>" data-type="<?php echo htmlspecialchars($event['type']); ?>" data-start="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['start']))); ?>" data-end="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($event['end']))); ?>" class="btn btn-sm btn-warning edit-event-btn"> <i class="fas fa-edit"></i></button>
                                      <a href="my_schedule.php?delete=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                  </td>
                             </tr>
                         <?php  endif; endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Brak zaplanowanych wydarzeń.</td>
                         </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">
                         <i class="fas fa-plus"></i> Dodaj Wydarzenie
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                 </div>
                 <div class="modal-body">
                   <form method="post" id="addEventForm">
                    <input type="hidden" name="id" id="modal_event_id">
                        <div class="form-group">
                            <label for="type">Typ:</label>
                            <select name="type" id="modal_type" class="form-control" required>
                                 <?php foreach($settings['eventTypes'] as $key => $type): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                                 <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start">Start:</label>
                            <input type="datetime-local" class="form-control" name="start" id="modal_start" required>
                        </div>
                        <div class="form-group">
                            <label for="end">Koniec:</label>
                             <input type="datetime-local" class="form-control" name="end" id="modal_end" required>
                        </div>
                    </form>
                </div>
               <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
                    <button type="submit" class="btn btn-primary" form="addEventForm">
                         <i class="fas fa-save"></i> Zapisz
                    </button>
                </div>
          </div>
        </div>
    </div>


<script>
 document.addEventListener('DOMContentLoaded', function () {
      const addEventForm = document.getElementById('addEventForm');
       if(addEventForm){
             addEventForm.addEventListener('submit', function (event) {
            let startInput = document.getElementById('modal_start');
              let endInput = document.getElementById('modal_end');
            let typeInput = document.getElementById('modal_type');
            let error = false;

            if (!startInput.value) {
                alert("Proszę podać datę i godzinę startu.");
                error = true;
            }

            if (!endInput.value) {
                alert("Proszę podać datę i godzinę końca.");
                 error = true;
            }

             if (!typeInput.value) {
                alert("Proszę wybrać typ wydarzenia.");
                 error = true;
            }

            if (startInput.value >= endInput.value && startInput.value && endInput.value) {
                alert("Data startu musi być wcześniejsza niż data końca.");
                error = true;
            }


            if(error){
                event.preventDefault();
            }
        });
       }
    });


    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
             initialView: 'dayGridMonth',
            events: <?php echo json_encode($myEvents); ?>,
            eventDidMount: function(info) {
                if (info.event.backgroundColor) {
                    info.el.style.backgroundColor = info.event.backgroundColor;
                    info.el.style.borderColor = info.event.backgroundColor;
                }
            },
            dayCellDidMount: function(info) {
                if (info.date.getDay() === 0 || info.date.getDay() === 6) {
                    info.el.style.backgroundColor = "rgba(190,190,190,0.3)";
                }
            },
            locale: 'pl',
             headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            eventClick: function(info) {
                  var eventId = info.event.id;
                  $('#modal_event_id').val(eventId);
                     var event = info.event;
                      $('#modal_type').val(event.title);
                    $('#addEventModal').modal('show');
                    console.log(event)
              }
        });
        calendar.render();
     <?php if($modal && isset($editEvent)): ?>
            $('#addEventModal').modal('show');
              $('#modal_event_id').val('<?php echo htmlspecialchars($editEvent['id']); ?>');
              $('#modal_type').val('<?php echo htmlspecialchars($editEvent['type']); ?>');
             $('#modal_start').val('<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($editEvent['start']))); ?>');
                $('#modal_end').val('<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($editEvent['end']))); ?>');
         <?php endif; ?>
           $('.edit-event-btn').click(function () {
           $('#modal_event_id').val($(this).data('id'));
             $('#modal_type').val($(this).data('type'));
           $('#modal_start').val($(this).data('start'));
           $('#modal_end').val($(this).data('end'));
            $('#addEventModal').modal('show');
        });
    });
</script>
<?php require 'components/footer.php'; ?>