<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'lib/functions.php';

if (isset($_GET['logout'])) {
    logoutUser();
    header('Location: index.php');
    exit;
}

if(isLoggedIn()){
    $events = getSchedule();
    $formattedEvents = array();

    foreach ($events as $event) {
        $color = '';
        $settings = getSettings();
        if(isset($settings['eventTypes'][$event['type']])){
            $color = $settings['eventTypes'][$event['type']]['color'];
        }
         $formattedEvents[] = array(
            'title' => getUserName($event['userId']).' ('.$settings['eventTypes'][$event['type']]['short'].')',
            'start' => $event['start'],
            'end' => $event['end'],
             'color' => $color
        );
    }

    require 'components/header.php';
        ?>
    <div id="calendar"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo json_encode($formattedEvents); ?>,
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
                  }
            });
            calendar.render();
        });
    </script>
    <?php
    require 'components/footer.php';
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if(loginUser($username, $password)){
        header('Location: index.php');
    } else {
        $error = "Nieprawidłowe dane logowania";
    }
}

require 'components/header.php';
?>
    <div class="row justify-content-center">
        <div class="col-md-6">
        <h2 class="text-center">
      <i class="fas fa-sign-in-alt"></i> Logowanie
    </h2>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Użytkownik:</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Hasło:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">
    <i class="fas fa-sign-in-alt"></i> Zaloguj
</button>
            </form>
        </div>
    </div>
<?php require 'components/footer.php'; ?>