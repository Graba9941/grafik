<?php
require 'lib/functions.php';

if(!isAdmin()){
    header('Location: index.php');
    exit;
}

$settings = getSettings();
$users = getUsers();
$error = '';
$success = '';

// Obsługa dodawania użytkownika
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])){
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? htmlspecialchars(trim($_POST['role'])) : '';
    
    if($username && $password && $role){
      $userData = array(
          'username' => $username,
          'password' => md5($password),
          'role' => $role
      );
      addUser($userData);
      $success = "Użytkownik został dodany.";
    } else {
      $error = "Wszystkie pola muszą być wypełnione.";
    }
}

// Obsługa edycji użytkownika
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])){
    $userId = isset($_POST['user_id']) ? htmlspecialchars(trim($_POST['user_id'])) : '';
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? htmlspecialchars(trim($_POST['role'])) : '';
    
    if($userId && $username && $password && $role){
      $userData = array(
          'username' => $username,
          'password' => md5($password),
          'role' => $role
      );
      updateUser($userId, $userData);
      $success = "Użytkownik został zaktualizowany.";
    } else {
      $error = "Wszystkie pola muszą być wypełnione.";
    }
}

// Obsługa usuwania użytkownika
if(isset($_GET['delete_user'])){
   $userId = isset($_GET['delete_user']) ? htmlspecialchars(trim($_GET['delete_user'])) : '';
    deleteUser($userId);
    $success = "Użytkownik został usunięty.";
}

require 'components/header.php';
?>
<h2>Panel Administratora</h2>
<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<div class="row">
    <div class="col-md-12">
        <h3>Zarządzanie Użytkownikami</h3>
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">
            Dodaj Użytkownika
        </button>
        <div class="table-responsive">
             <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Użytkownik</th>
                        <th>Rola</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($users) > 0): ?>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <button data-id="<?php echo htmlspecialchars($user['id']); ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-role="<?php echo htmlspecialchars($user['role']); ?>" class="btn btn-sm btn-warning edit-user-btn">Edytuj</button>
                                      <a href="admin.php?delete_user=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-sm btn-danger">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Brak użytkowników.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
             </table>
        </div>
    </div>
</div>
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Dodaj Użytkownika</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" id="addUserForm">
                    <input type="hidden" name="user_id" id="modal_user_id">
                    <div class="form-group">
                        <label for="username">Użytkownik:</label>
                        <input type="text" class="form-control" name="username" id="modal_username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Hasło:</label>
                        <input type="password" class="form-control" name="password" id="modal_password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Rola:</label>
                        <select name="role" id="modal_role" class="form-control" required>
                            <option value="user">Użytkownik</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
                <button type="submit" class="btn btn-primary" form="addUserForm" name="add_user">Zapisz</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.edit-user-btn').click(function () {
            $('#modal_user_id').val($(this).data('id'));
            $('#modal_username').val($(this).data('username'));
             $('#modal_role').val($(this).data('role'));
            $('#addUserModal').modal('show');
        });
    });
</script>
<?php require 'components/footer.php'; ?>