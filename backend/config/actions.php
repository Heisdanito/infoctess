<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once '../connection/connection.php';

// Get user role from session
$user_role = $_SESSION['user_role'] ?? $_SESSION['roles'] ?? 'user';
$user_id = $_SESSION['student_id'] ?? '';

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// If JSON data exists, use it
if ($data && isset($data['action'])) {
    $action = $data['action'];
}

if (empty($action)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No action specified'
    ]);
    exit;
}

// Check if user is logged in
if (empty($user_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit;
}

// Define permission levels
$admin_roles = ['admin'];
$editor_roles = ['admin', 'rep', 'ta', 'lec']; // Can delete sessions
$viewer_roles = ['admin', 'rep', 'ta', 'lec', 'user']; // Can view

// Route to appropriate function based on action
switch ($action) {
    // Session Actions - Can be done by admin, rep, ta, lec
    case 'delete_session':
    case 'deactivate_session':
    case 'reactivate_session':
    case 'create_session':
    case 'update_session':
        checkPermission($editor_roles, $user_role, $action);
        handleSessionActions($action, $data, $conn, $user_id, $user_role);
        break;
    
    // Student Actions - Only admin
    case 'edit_student':
    case 'delete_student':
    case 'add_student':
    case 'update_student_role':
    case 'edit_student_name':
    case 'edit_student_course':
        checkPermission($admin_roles, $user_role, $action);
        handleStudentActions($action, $data, $conn, $user_id);
        break;
    
    // Course Actions - Only admin
    case 'edit_course':
    case 'delete_course':
    case 'add_course':
    case 'update_course':
        checkPermission($admin_roles, $user_role, $action);
        handleCourseActions($action, $data, $conn, $user_id);
        break;
    
    // Group Actions - Only admin
    case 'edit_group':
    case 'delete_group':
    case 'add_group':
    case 'update_group':
        checkPermission($admin_roles, $user_role, $action);
        handleGroupActions($action, $data, $conn, $user_id);
        break;
    
    // Attendance Actions - Editors can delete, only admin can edit
    case 'delete_attendance':
        checkPermission($editor_roles, $user_role, $action);
        handleAttendanceActions($action, $data, $conn, $user_id);
        break;
    
    case 'edit_attendance':
        checkPermission($admin_roles, $user_role, $action);
        handleAttendanceActions($action, $data, $conn, $user_id);
        break;
    
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action: ' . $action
        ]);
        break;
}

// Function to check permissions
function checkPermission($allowed_roles, $user_role, $action) {
    if (!in_array($user_role, $allowed_roles)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Permission denied. You need ' . implode(', ', $allowed_roles) . ' role to perform: ' . $action,
            'your_role' => $user_role
        ]);
        exit;
    }
}

// Handle all session-related actions
function handleSessionActions($action, $data, $conn, $user_id, $user_role) {
    switch ($action) {
        case 'delete_session':
            deleteSession($data, $conn, $user_id, $user_role);
            break;
        case 'deactivate_session':
            deactivateSession($data, $conn, $user_id, $user_role);
            break;
        case 'reactivate_session':
            reactivateSession($data, $conn, $user_id, $user_role);
            break;
        case 'create_session':
            createSession($data, $conn, $user_id);
            break;
        case 'update_session':
            updateSession($data, $conn, $user_id, $user_role);
            break;
    }
}

// Delete session (soft delete or hard delete based on role)
function deleteSession($data, $conn, $user_id, $user_role) {
    if (empty($data['session_id']) && empty($data['session_code'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session ID or code required'
        ]);
        return;
    }
    
    $session_id = isset($data['session_id']) ? mysqli_real_escape_string($conn, $data['session_id']) : '';
    $session_code = isset($data['session_code']) ? mysqli_real_escape_string($conn, $data['session_code']) : '';
    
    // Build where clause
    $where = "";
    if (!empty($session_id)) {
        $where = "id = '$session_id'";
    } else {
        $where = "session_code = '$session_code'";
    }
    
    // Check if user has permission to delete this session
    if ($user_role === 'admin') {
        // Admin can hard delete
        // First delete related attendance records
        $session_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT session_code FROM qrcode WHERE $where"));
        if ($session_info) {
            $code = $session_info['session_code'];
            mysqli_query($conn, "DELETE FROM attendance WHERE session_code = '$code'");
        }
        
        // Then delete the session
        $query = "DELETE FROM qrcode WHERE $where";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Session and related attendance deleted permanently'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete session: ' . mysqli_error($conn)
            ]);
        }
    } else {
        // Rep, TA, Lec can only soft delete (deactivate)
        $query = "UPDATE qrcode SET is_active = '0' WHERE $where";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Session deactivated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to deactivate session: ' . mysqli_error($conn)
            ]);
        }
    }
}

// Deactivate session
function deactivateSession($data, $conn, $user_id, $user_role) {
    if (empty($data['session_id']) && empty($data['session_code'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session ID or code required'
        ]);
        return;
    }
    
    $session_id = isset($data['session_id']) ? mysqli_real_escape_string($conn, $data['session_id']) : '';
    $session_code = isset($data['session_code']) ? mysqli_real_escape_string($conn, $data['session_code']) : '';
    
    $where = "";
    if (!empty($session_id)) {
        $where = "id = '$session_id'";
    } else {
        $where = "session_code = '$session_code'";
    }
    
    $query = "UPDATE qrcode SET is_active = '0' WHERE $where";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Session deactivated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to deactivate session: ' . mysqli_error($conn)
        ]);
    }
}

// Reactivate session
function reactivateSession($data, $conn, $user_id, $user_role) {
    if (empty($data['session_id']) && empty($data['session_code'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session ID or code required'
        ]);
        return;
    }
    
    $session_id = isset($data['session_id']) ? mysqli_real_escape_string($conn, $data['session_id']) : '';
    $session_code = isset($data['session_code']) ? mysqli_real_escape_string($conn, $data['session_code']) : '';
    
    $where = "";
    if (!empty($session_id)) {
        $where = "id = '$session_id'";
    } else {
        $where = "session_code = '$session_code'";
    }
    
    $query = "UPDATE qrcode SET is_active = '1' WHERE $where";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Session reactivated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to reactivate session: ' . mysqli_error($conn)
        ]);
    }
}

// Create new session
function createSession($data, $conn, $user_id) {
    $required = ['course_id', 'group_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode([
                'status' => 'error',
                'message' => "$field is required"
            ]);
            return;
        }
    }
    
    // Generate session code
    $session_code = rand(1000, 9999) . 'UEW' . rand(0, 9) . 'QR' . rand(100000, 999999) . 'heis';
    $qrcode = 'QRCodeForUEW101att' . rand(1000, 9999) . '^&*sd%gh%h3!#e1i%2s$' . rand(1, 9);
    
    $course_id = mysqli_real_escape_string($conn, $data['course_id']);
    $group_id = mysqli_real_escape_string($conn, $data['group_id']);
    $latitude = isset($data['latitude']) ? mysqli_real_escape_string($conn, $data['latitude']) : '';
    $longitude = isset($data['longitude']) ? mysqli_real_escape_string($conn, $data['longitude']) : '';
    $duration = isset($data['duration']) ? intval($data['duration']) : 60;
    
    // Calculate expire time
    $expire_at = date('Y-m-d H:i:s', strtotime("+$duration minutes"));
    
    $query = "INSERT INTO qrcode (QRcode, session_code, longitude, latitude, serial_status, is_active, created_by, created_at, expire_at, group_id, course_id) 
              VALUES ('$qrcode', '$session_code', '$longitude', '$latitude', 'qrcode', '1', '$user_id', NOW(), '$expire_at', '$group_id', '$course_id')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Session created successfully',
            'data' => [
                'session_code' => $session_code,
                'qrcode' => $qrcode,
                'expire_at' => $expire_at
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create session: ' . mysqli_error($conn)
        ]);
    }
}

// Update session
function updateSession($data, $conn, $user_id, $user_role) {
    if (empty($data['session_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session ID required'
        ]);
        return;
    }
    
    $session_id = mysqli_real_escape_string($conn, $data['session_id']);
    $updates = [];
    
    if (isset($data['course_id'])) {
        $course_id = mysqli_real_escape_string($conn, $data['course_id']);
        $updates[] = "course_id = '$course_id'";
    }
    if (isset($data['group_id'])) {
        $group_id = mysqli_real_escape_string($conn, $data['group_id']);
        $updates[] = "group_id = '$group_id'";
    }
    if (isset($data['latitude'])) {
        $latitude = mysqli_real_escape_string($conn, $data['latitude']);
        $updates[] = "latitude = '$latitude'";
    }
    if (isset($data['longitude'])) {
        $longitude = mysqli_real_escape_string($conn, $data['longitude']);
        $updates[] = "longitude = '$longitude'";
    }
    
    if (empty($updates)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No fields to update'
        ]);
        return;
    }
    
    $query = "UPDATE qrcode SET " . implode(', ', $updates) . " WHERE id = '$session_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Session updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update session: ' . mysqli_error($conn)
        ]);
    }
}

// Handle student actions (admin only)
function handleStudentActions($action, $data, $conn, $user_id) {
    switch ($action) {
        case 'edit_student':
        case 'edit_student_name':
            editStudentName($data, $conn);
            break;
        case 'edit_student_course':
            editStudentCourse($data, $conn);
            break;
        case 'delete_student':
            deleteStudent($data, $conn);
            break;
        case 'add_student':
            addStudent($data, $conn);
            break;
        case 'update_student_role':
            updateStudentRole($data, $conn);
            break;
    }
}

function editStudentName($data, $conn) {
    if (empty($data['student_id']) || empty($data['student_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID and name required'
        ]);
        return;
    }
    
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $student_name = mysqli_real_escape_string($conn, $data['student_name']);
    
    $query = "UPDATE students SET student_name = '$student_name', updated_at = NOW() WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Student name updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update student: ' . mysqli_error($conn)
        ]);
    }
}

function editStudentCourse($data, $conn) {
    if (empty($data['student_id']) || empty($data['programme'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID and programme required'
        ]);
        return;
    }
    
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $programme = mysqli_real_escape_string($conn, $data['programme']);
    $group_id = isset($data['group_id']) ? mysqli_real_escape_string($conn, $data['group_id']) : '';
    
    $query = "UPDATE students SET programme = '$programme', group_id = '$group_id', updated_at = NOW() WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Student course updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update student: ' . mysqli_error($conn)
        ]);
    }
}

function deleteStudent($data, $conn) {
    if (empty($data['student_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID required'
        ]);
        return;
    }
    
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    
    // Check if student has attendance records
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE student_id = '$student_id'");
    $count = mysqli_fetch_assoc($check)['count'];
    
    if ($count > 0) {
        // Soft delete - just deactivate
        $query = "UPDATE students SET active = '0', updated_at = NOW() WHERE student_id = '$student_id'";
    } else {
        // Hard delete
        $query = "DELETE FROM students WHERE student_id = '$student_id'";
    }
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Student deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete student: ' . mysqli_error($conn)
        ]);
    }
}

function addStudent($data, $conn) {
    $required = ['student_id', 'student_name', 'programme'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode([
                'status' => 'error',
                'message' => "$field is required"
            ]);
            return;
        }
    }
    
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $student_name = mysqli_real_escape_string($conn, $data['student_name']);
    $programme = mysqli_real_escape_string($conn, $data['programme']);
    $group_id = isset($data['group_id']) ? mysqli_real_escape_string($conn, $data['group_id']) : '';
    $roles = isset($data['roles']) ? mysqli_real_escape_string($conn, $data['roles']) : 'user';
    $student_mail = isset($data['student_mail']) ? mysqli_real_escape_string($conn, $data['student_mail']) : $student_id . '@stu.uew.edu.gh';
    
    // Check if student already exists
    $check = mysqli_query($conn, "SELECT student_id FROM students WHERE student_id = '$student_id'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID already exists'
        ]);
        return;
    }
    
    $query = "INSERT INTO students (student_id, student_name, student_mail, programme, roles, active, created_at, updated_at, group_id) 
              VALUES ('$student_id', '$student_name', '$student_mail', '$programme', '$roles', '1', NOW(), NOW(), '$group_id')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Student added successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add student: ' . mysqli_error($conn)
        ]);
    }
}

function updateStudentRole($data, $conn) {
    if (empty($data['student_id']) || empty($data['roles'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID and role required'
        ]);
        return;
    }
    
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $roles = mysqli_real_escape_string($conn, $data['roles']);
    
    $query = "UPDATE students SET roles = '$roles', updated_at = NOW() WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Student role updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update role: ' . mysqli_error($conn)
        ]);
    }
}

// Handle course actions (admin only)
function handleCourseActions($action, $data, $conn, $user_id) {
    switch ($action) {
        case 'edit_course':
        case 'update_course':
            updateCourse($data, $conn);
            break;
        case 'delete_course':
            deleteCourse($data, $conn);
            break;
        case 'add_course':
            addCourse($data, $conn);
            break;
    }
}

function updateCourse($data, $conn) {
    if (empty($data['course_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Course ID required'
        ]);
        return;
    }
    
    $course_id = mysqli_real_escape_string($conn, $data['course_id']);
    $updates = [];
    
    if (isset($data['course_name'])) {
        $course_name = mysqli_real_escape_string($conn, $data['course_name']);
        $updates[] = "course_name = '$course_name'";
    }
    if (isset($data['programme_id'])) {
        $programme_id = mysqli_real_escape_string($conn, $data['programme_id']);
        $updates[] = "programme_id = '$programme_id'";
    }
    if (isset($data['group_id'])) {
        $group_id = mysqli_real_escape_string($conn, $data['group_id']);
        $updates[] = "group_id = '$group_id'";
    }
    
    $updates[] = "updated_at = NOW()";
    
    $query = "UPDATE courses SET " . implode(', ', $updates) . " WHERE course_id = '$course_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Course updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update course: ' . mysqli_error($conn)
        ]);
    }
}

function deleteCourse($data, $conn) {
    if (empty($data['course_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Course ID required'
        ]);
        return;
    }
    
    $course_id = mysqli_real_escape_string($conn, $data['course_id']);
    
    // Check if course has sessions
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM qrcode WHERE course_id = '$course_id'");
    $count = mysqli_fetch_assoc($check)['count'];
    
    if ($count > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot delete course with existing sessions'
        ]);
        return;
    }
    
    $query = "DELETE FROM courses WHERE course_id = '$course_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Course deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete course: ' . mysqli_error($conn)
        ]);
    }
}

function addCourse($data, $conn) {
    $required = ['course_id', 'course_name', 'programme_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode([
                'status' => 'error',
                'message' => "$field is required"
            ]);
            return;
        }
    }
    
    $course_id = mysqli_real_escape_string($conn, $data['course_id']);
    $course_name = mysqli_real_escape_string($conn, $data['course_name']);
    $programme_id = mysqli_real_escape_string($conn, $data['programme_id']);
    $group_id = isset($data['group_id']) ? mysqli_real_escape_string($conn, $data['group_id']) : '';
    
    // Check if course already exists
    $check = mysqli_query($conn, "SELECT course_id FROM courses WHERE course_id = '$course_id'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Course ID already exists'
        ]);
        return;
    }
    
    $query = "INSERT INTO courses (course_id, course_name, programme_id, group_id, created_at, updated_at) 
              VALUES ('$course_id', '$course_name', '$programme_id', '$group_id', NOW(), NOW())";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Course added successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add course: ' . mysqli_error($conn)
        ]);
    }
}

// Handle group actions (admin only)
function handleGroupActions($action, $data, $conn, $user_id) {
    switch ($action) {
        case 'edit_group':
        case 'update_group':
            updateGroup($data, $conn);
            break;
        case 'delete_group':
            deleteGroup($data, $conn);
            break;
        case 'add_group':
            addGroup($data, $conn);
            break;
    }
}

function updateGroup($data, $conn) {
    if (empty($data['group_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Group ID required'
        ]);
        return;
    }
    
    $group_id = mysqli_real_escape_string($conn, $data['group_id']);
    $updates = [];
    
    if (isset($data['group_name'])) {
        $group_name = mysqli_real_escape_string($conn, $data['group_name']);
        $updates[] = "group_name = '$group_name'";
    }
    if (isset($data['status'])) {
        $status = mysqli_real_escape_string($conn, $data['status']);
        $updates[] = "status = '$status'";
    }
    if (isset($data['course_id'])) {
        $course_id = mysqli_real_escape_string($conn, $data['course_id']);
        $updates[] = "course_id = '$course_id'";
    }
    
    $updates[] = "updated_at = NOW()";
    
    $query = "UPDATE `groups` SET " . implode(', ', $updates) . " WHERE group_id = '$group_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Group updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update group: ' . mysqli_error($conn)
        ]);
    }
}

function deleteGroup($data, $conn) {
    if (empty($data['group_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Group ID required'
        ]);
        return;
    }
    
    $group_id = mysqli_real_escape_string($conn, $data['group_id']);
    
    // Check if group has students
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE group_id = '$group_id'");
    $count = mysqli_fetch_assoc($check)['count'];
    
    if ($count > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot delete group with assigned students'
        ]);
        return;
    }
    
    $query = "DELETE FROM `groups` WHERE group_id = '$group_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Group deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete group: ' . mysqli_error($conn)
        ]);
    }
}

function addGroup($data, $conn) {
    $required = ['group_name', 'programme_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode([
                'status' => 'error',
                'message' => "$field is required"
            ]);
            return;
        }
    }
    
    $group_name = mysqli_real_escape_string($conn, $data['group_name']);
    $programme_id = mysqli_real_escape_string($conn, $data['programme_id']);
    $course_id = isset($data['course_id']) ? mysqli_real_escape_string($conn, $data['course_id']) : '';
    $group_rep_id = isset($data['group_rep_id']) ? mysqli_real_escape_string($conn, $data['group_rep_id']) : '';
    $group_rep_id_2 = isset($data['group_rep_id_2']) ? mysqli_real_escape_string($conn, $data['group_rep_id_2']) : '';
    $academic_year = isset($data['academic_year']) ? mysqli_real_escape_string($conn, $data['academic_year']) : date('Y');
    
    // Generate group_id
    $group_id_query = "SELECT MAX(CAST(group_id AS UNSIGNED)) as max_id FROM group_main WHERE programme_id = '$programme_id'";
    $group_id_result = mysqli_query($conn, $group_id_query);
    $group_id_row = mysqli_fetch_assoc($group_id_result);
    $new_group_id = ($group_id_row['max_id'] ?? 0) + 1;
    
    // Insert into group_main
    $insert_main = "INSERT INTO group_main (group_id, group_name, programme_id) VALUES ('$new_group_id', '$group_name', '$programme_id')";
    mysqli_query($conn, $insert_main);
    
    // Insert into groups
    $query = "INSERT INTO `groups` (group_name, status, group_rep_id, group_rep_id_2, programme_id, course_id, academic_year, created_at, updated_at, group_id) 
              VALUES ('$group_name', 'active', '$group_rep_id', '$group_rep_id_2', '$programme_id', '$course_id', '$academic_year', NOW(), NOW(), '$new_group_id')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Group added successfully',
            'data' => ['group_id' => $new_group_id]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add group: ' . mysqli_error($conn)
        ]);
    }
}

// Handle attendance actions
function handleAttendanceActions($action, $data, $conn, $user_id) {
    switch ($action) {
        case 'delete_attendance':
            deleteAttendance($data, $conn);
            break;
        case 'edit_attendance':
            editAttendance($data, $conn);
            break;
    }
}

function deleteAttendance($data, $conn) {
    if (empty($data['attendance_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Attendance ID required'
        ]);
        return;
    }
    
    $attendance_id = mysqli_real_escape_string($conn, $data['attendance_id']);
    
    $query = "DELETE FROM attendance WHERE id = '$attendance_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance record deleted'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete attendance: ' . mysqli_error($conn)
        ]);
    }
}

function editAttendance($data, $conn) {
    if (empty($data['attendance_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Attendance ID required'
        ]);
        return;
    }
    
    $attendance_id = mysqli_real_escape_string($conn, $data['attendance_id']);
    $updates = [];
    
    if (isset($data['student_id'])) {
        $student_id = mysqli_real_escape_string($conn, $data['student_id']);
        $updates[] = "student_id = '$student_id'";
    }
    if (isset($data['session_code'])) {
        $session_code = mysqli_real_escape_string($conn, $data['session_code']);
        $updates[] = "session_code = '$session_code'";
    }
    if (isset($data['latitude'])) {
        $latitude = mysqli_real_escape_string($conn, $data['latitude']);
        $updates[] = "latitude = '$latitude'";
    }
    if (isset($data['longitude'])) {
        $longitude = mysqli_real_escape_string($conn, $data['longitude']);
        $updates[] = "longitude = '$longitude'";
    }
    
    if (empty($updates)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No fields to update'
        ]);
        return;
    }
    
    $query = "UPDATE attendance SET " . implode(', ', $updates) . " WHERE id = '$attendance_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update attendance: ' . mysqli_error($conn)
        ]);
    }
}

mysqli_close($conn);
?>