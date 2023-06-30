<?php
function encryptPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword)
{
    return password_verify($password, $hashedPassword);
}

function redirect($location)
{
    header("Location: $location");
    exit;
}

function authenticate($conn, $username, $password)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && verifyPassword($password, $user['password'])) {
        unset($user['password']);
        return $user;
    }

    return null;
}

function hasPermission($user, $role)
{
    return $user['role'] === $role;
}

function isAdmin($user)
{
    return hasPermission($user, 'administrator');
}

function isTeacher($user)
{
    return hasPermission($user, 'nauczyciel');
}

function isStudent($user)
{
    return hasPermission($user, 'uczen');
}

function getAllUsers($conn)
{
    $stmt = $conn->query("SELECT * FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($conn, $userId)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createUser($conn, $username, $password, $role)
{
    $hashedPassword = encryptPassword($password);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    return $stmt->execute();
}

function updateUser($conn, $userId, $username, $password, $role)
{
    $hashedPassword = encryptPassword($password);
    $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password, role = :role WHERE id = :id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':id', $userId);
    return $stmt->execute();
}


function deleteUser($conn, $userId) {
    
    // Usuń oceny powiązane z użytkownikiem
    $deleteGradesQuery = "DELETE FROM grades WHERE student_id = :user_id";
    $deleteGradesStmt = $conn->prepare($deleteGradesQuery);
    $deleteGradesStmt->bindParam(':user_id', $userId);
    $deleteGradesStmt->execute();

       // Usuń przedmioty powiązane z użytkownikiem jako nauczyciel
    $deleteSubjectsQuery = "DELETE FROM subjects WHERE teacher_id = :user_id";
    $deleteSubjectsStmt = $conn->prepare($deleteSubjectsQuery);
    $deleteSubjectsStmt->bindParam(':user_id', $userId);
    $deleteSubjectsStmt->execute();

    // Usuń użytkownika
    $deleteUserQuery = "DELETE FROM users WHERE id = :user_id";
    $deleteUserStmt = $conn->prepare($deleteUserQuery);
    $deleteUserStmt->bindParam(':user_id', $userId);
    $deleteUserStmt->execute();
}






function addSubject($conn, $teacherId, $subject)
{
    $query = "INSERT INTO subjects (teacher_id, name) VALUES (:teacherId, :subject)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
    $stmt->execute();
}


function addGrade($conn, $studentId, $subjectId, $grade) {
    $query = "INSERT INTO grades (student_id, subject_id, grade) VALUES (:studentId, :subjectId, :grade)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':studentId', $studentId, PDO::PARAM_INT);
    $stmt->bindValue(':subjectId', $subjectId, PDO::PARAM_INT);
    $stmt->bindValue(':grade', $grade, PDO::PARAM_INT);
    $stmt->execute();
}


function getSubjectGrades($conn, $subjectId) {
    $query = "SELECT * FROM grades WHERE subject_id = :subjectId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':subjectId', $subjectId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStudentName($conn, $studentId) {
    $query = "SELECT username FROM users WHERE id = :studentId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':studentId', $studentId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['username'];
}

function getAllStudents($conn) {
    $query = "SELECT * FROM users WHERE role = 'uczen'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStudentSubjects($conn, $studentId) {
    $query = "SELECT subjects.id, subjects.name
              FROM subjects
              INNER JOIN grades ON subjects.id = grades.subject_id
              WHERE grades.student_id = :student_id
              GROUP BY subjects.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getStudentGrades($conn, $studentId) {
    $query = "SELECT grades.id, grades.student_id, grades.subject_id, grades.grade, grades.last_modified, subjects.name 
              FROM grades 
              INNER JOIN subjects ON grades.subject_id = subjects.id 
              WHERE grades.student_id = :studentId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function updateGradeTimestamp($conn, $studentId, $subjectId) {
    $stmt = $conn->prepare("UPDATE grades SET last_modified = NOW() WHERE student_id = :studentId AND subject_id = :subjectId");
    $stmt->bindParam(':studentId', $studentId);
    $stmt->bindParam(':subjectId', $subjectId);
    $stmt->execute();
}

// Pobierz oceny ucznia dla konkretnego przedmiotu
function getStudentGradesBySubject($conn, $studentId, $subjectId) {
    $query = "SELECT grades.id, grades.student_id, grades.subject_id, grades.grade, grades.last_modified, subjects.name 
              FROM grades 
              INNER JOIN subjects ON grades.subject_id = subjects.id 
              WHERE grades.student_id = :studentId AND grades.subject_id = :subjectId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $stmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getSubjectName($conn, $subjectId) {
    $query = "SELECT name FROM subjects WHERE id = :subject_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['name'] : '';
}


function getTeacherSubjects($conn, $teacherId)
{
    $query = "SELECT * FROM subjects WHERE teacher_id = :teacherId";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $subjects;
}



function updateGrade($conn, $gradeId, $newGrade)
{
    $query = "UPDATE grades SET grade = :grade WHERE id = :gradeId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':grade', $newGrade, PDO::PARAM_STR);
    $stmt->bindParam(':gradeId', $gradeId, PDO::PARAM_INT);
    $stmt->execute();
}


function deleteGrade($conn, $gradeId)
{
    $query = "DELETE FROM grades WHERE id = :gradeId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':gradeId', $gradeId, PDO::PARAM_INT);
    $stmt->execute();
}


function updateSubject($conn, $subjectId, $newSubjectName)
{
    $query = "UPDATE subjects SET name = :subjectName WHERE id = :subjectId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':subjectName', $newSubjectName, PDO::PARAM_STR);
    $stmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
    $stmt->execute();
}


function deleteSubject($conn, $subjectId)
{
    $query = "DELETE FROM subjects WHERE id = :subjectId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
    $stmt->execute();
}





?>
