<?php
session_start();

require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user']) || !isStudent($_SESSION['user'])) {
    redirect('index.php');
}

$user = $_SESSION['user'];
$studentId = $user['id'];

$subjects = getStudentSubjects($conn, $studentId);
$selectedSubjectId = isset($_POST['subject_id']) ? $_POST['subject_id'] : 'all';

if (isset($_POST['add_grade'])) {
    $subjectId = $_POST['subject_id'];
    $grade = $_POST['grade'];

    addGrade($conn, $studentId, $subjectId, $grade);
}

$grades = array();
if ($selectedSubjectId === 'all') {
    $grades = getStudentGrades($conn, $studentId);
} else {
    $grades = getStudentGradesBySubject($conn, $studentId, $selectedSubjectId);
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dziennik Lekcyjny - Uczeń</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1>Witaj, <?php echo $user['username']; ?>!</h1>

        <h2>Przedmioty</h2>
        <form method="POST">
            <div class="form-group">
                <label for="subjectFilter">Wybierz przedmiot:</label>
                <select class="form-control" id="subjectFilter" name="subject_id">
                    <option value="all">Wszystkie</option>
                    <?php foreach ($subjects as $subject) : ?>
                        <option value="<?php echo $subject['id']; ?>" <?php if ($subject['id'] === $selectedSubjectId) echo 'selected'; ?>>
                            <?php echo $subject['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtruj</button>
        </form>

        <h2>Oceny</h2>
        <?php if (count($grades) > 0) : ?>
            <table class="table">
                <tr>
                    <th>Nazwa przedmiotu</th>
                    <th>Ocena</th>
                    <th>Data edycji</th>
                </tr>
                <?php foreach ($grades as $grade) : ?>
                    <tr>
                        <td><?php echo getSubjectName($conn, $grade['subject_id']); ?></td>
                        <td><?php echo $grade['grade']; ?></td>
                        <td><?php echo $grade['last_modified']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else : ?>
            <p>Brak ocen.</p>
        <?php endif; ?>

        <a href="logout.php">Wyloguj</a>
    </div>
</body>

</html>
