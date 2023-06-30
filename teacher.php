<?php
session_start();

require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'nauczyciel') {
    redirect('index.php');
}

$user = $_SESSION['user'];

if (isset($_POST['add_subject'])) {
    $subjectName = $_POST['subject_name'];
    addSubject($conn, $user['id'], $subjectName);
}

if (isset($_POST['add_grade'])) {
    // Pobierz wartości z formularza
    $studentId = $_POST['student_id'];
    $subjectId = $_POST['subject_id'];
    $gradeValue = $_POST['grade'];
    
    // Dodaj nową ocenę do bazy danych
    $stmt = $conn->prepare("INSERT INTO grades (student_id, subject_id, grade, last_modified) VALUES (:studentId, :subjectId, :gradeValue, NOW())");
    $stmt->bindParam(':studentId', $studentId);
    $stmt->bindParam(':subjectId', $subjectId);
    $stmt->bindParam(':gradeValue', $gradeValue);
    $stmt->execute();
}

if (isset($_POST['update_grade'])) {
    // Pobierz wartości z formularza
    $gradeId = $_POST['grade_id'];
    $newGradeValue = $_POST['new_grade'];
    
    // Zaktualizuj ocenę w bazie danych
    $stmt = $conn->prepare("UPDATE grades SET grade = :newGradeValue, last_modified = NOW() WHERE id = :gradeId");
    $stmt->bindParam(':newGradeValue', $newGradeValue);
    $stmt->bindParam(':gradeId', $gradeId);
    $stmt->execute();
}

if (isset($_POST['delete_grade'])) {
    $gradeId = $_POST['grade_id'];
    deleteGrade($conn, $gradeId);
}

if (isset($_POST['update_subject'])) {
    $subjectId = $_POST['subject_id'];
    $newSubjectName = $_POST['new_subject_name'];
    updateSubject($conn, $subjectId, $newSubjectName);
}

if (isset($_POST['delete_subject'])) {
    $subjectId = $_POST['subject_id'];
    deleteSubject($conn, $subjectId);
}

$subjects = getTeacherSubjects($conn, $user['id']);
$students = getAllStudents($conn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dziennik Lekcyjny - Panel Nauczyciela</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 20px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#tab-oceny").hide();
            
            $("#btn-przedmioty").click(function() {
                $("#tab-przedmioty").show();
                $("#tab-oceny").hide();
            });
            
            $("#btn-oceny").click(function() {
                $("#tab-oceny").show();
                $("#tab-przedmioty").hide();
            });
        });
    </script>
</head>

<body>
    <h1>Panel Nauczyciela</h1>

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" id="btn-oceny">Oceny</a>
            <li class="nav-item">
                <a class="nav-link" id="btn-przedmioty">Przedmioty</a>
            </li>
        </li>
        <li>
            <a href="dashboard.php" class="btn btn-secondary">Powrót do panelu</a>
        </li>
    </ul>

    <div id="tab-przedmioty">
        <h2>Dodaj przedmiot</h2>
        <form method="POST" class="form-inline mb-3">
            <div class="form-group">
                <input type="text" class="form-control mr-2" name="subject_name" placeholder="Nazwa przedmiotu" required>
                <button type="submit" class="btn btn-primary" name="add_subject">Dodaj przedmiot</button>
            </div>
        </form>

        <h2>Przedmioty</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nazwa przedmiotu</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <tr>
                        <td><?php echo $subject['id']; ?></td>
                        <td><?php echo $subject['name']; ?></td>
                        <td>
                            <form method="POST" class="d-inline-block">
                                <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" name="delete_subject">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="tab-oceny">
        <h2>Dodaj ocenę</h2>
        <form method="POST">
            <div class="form-group">
                <label for="student">Uczeń:</label>
                <select class="form-control" id="student" name="student_id" required>
                    <?php foreach ($students as $student) : ?>
                        <option value="<?php echo $student['id']; ?>"><?php echo $student['username']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subject">Przedmiot:</label>
                <select class="form-control" id="subject" name="subject_id" required>
                    <?php foreach ($subjects as $subject) : ?>
                        <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="grade">Ocena:</label>
                <input type="number" class="form-control" id="grade" name="grade" min="1" max="6" required>
            </div>

            <button type="submit" class="btn btn-primary" name="add_grade">Dodaj ocenę</button>
        </form>

        <h2>Oceny</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Uczeń</th>
                    <th>Przedmiot</th>
                    <th>Ocena</th>
                    <th>Data edycji</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <?php $grades = getSubjectGrades($conn, $subject['id']); ?>
                    <?php foreach ($grades as $grade) : ?>
                        <tr>
                            <td><?php echo $grade['id']; ?></td>
                            <td><?php echo getStudentName($conn, $grade['student_id']); ?></td>
                            <td><?php echo $subject['name']; ?></td>
                            <td><?php echo $grade['grade']; ?></td>
                            <td><?php echo $grade['last_modified']; ?></td>
                            <td>
                                <form method="POST" class="d-inline-block">
                                    <input type="hidden" name="grade_id" value="<?php echo $grade['id']; ?>">
                                    <input type="number" class="form-control mb-2 mr-sm-2" name="new_grade" value="<?php echo $grade['grade']; ?>" min="1" max="6" required>
                                    <button type="submit" class="btn btn-primary btn-sm mb-2" name="update_grade">Edytuj</button>
                                </form>
                                <form method="POST" class="d-inline-block">
                                    <input type="hidden" name="grade_id" value="<?php echo $grade['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" name="delete_grade">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

