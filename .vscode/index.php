<?php
session_start();
require_once '../../backend/connection/connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['student_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Verify admin role
$student_id = mysqli_real_escape_string($conn, $_SESSION['student_id']);
$admin_check = mysqli_query($conn, "SELECT roles, student_name FROM students WHERE student_id = '$student_id' AND active = '1'");
$admin_data = mysqli_fetch_assoc($admin_check);

if (!$admin_data || $admin_data['roles'] !== 'admin') {
    header("Location: ../../404.html");
    exit();
}

// Set admin info
$admin_name = $admin_data['student_name'];
$_SESSION['admin_authenticated'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Infoctess UEW - Admin Panel</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="../../vendors/typicons.font/font/typicons.css">
    <link rel="stylesheet" href="../../vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../../css/vertical-layout-light/style.css">
    <link rel="shortcut icon" href="../../images/favicon.png" />
    <style>
        .spinner {
            width: 30px;
            height: 30px;
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #4f6ef7;
            animation: spin 0.8s linear infinite;
            margin: 10px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loader {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            flex-direction: column;
            padding: 40px;
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        .stat-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <!-- Include Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <div class="container-fluid page-body-wrapper">
            <!-- Include Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <div class="main-panel">
                <div class="content-wrapper">
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h3 class="mb-0 font-weight-bold">Admin Dashboard</h3>
                            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
                        </div>
                        <div class="col-sm-6 text-right">
                            <button class="btn btn-primary" onclick="refreshAllData()">
                                <i class="typcn typcn-refresh"></i> Refresh Data
                            </button>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row" id="statsContainer">
                        <!-- Loaded via AJAX -->
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Quick Actions</h4>
                                    <button class="btn btn-primary mr-2" onclick="loadSection('create-session')">
                                        <i class="typcn typcn-plus"></i> New Session
                                    </button>
                                    <button class="btn btn-success mr-2" onclick="loadSection('students')">
                                        <i class="typcn typcn-group"></i> Manage Students
                                    </button>
                                    <button class="btn btn-info mr-2" onclick="loadSection('courses')">
                                        <i class="typcn typcn-book"></i> Manage Courses
                                    </button>
                                    <button class="btn btn-warning" onclick="loadSection('programmes')">
                                        <i class="typcn typcn-chart-bar"></i> Programmes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Content Sections -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <!-- Dashboard Section (Default) -->
                            <div id="dashboard-section" class="content-section active">
                                <div class="row">
                                    <div class="col-lg-6 grid-margin">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Recent Activity</h4>
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Time</th>
                                                                <th>Student</th>
                                                                <th>Session</th>
                                                                <th>Course</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="recentActivityBody">
                                                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 grid-margin">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="card-title">Last 7 Days Attendance</h4>
                                                <canvas id="attendanceChart" height="200"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Students Section -->
                            <div id="students-section" class="content-section">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="card-title mb-0">Student Management</h4>
                                            <button class="btn btn-primary" onclick="showAddStudentModal()">
                                                <i class="typcn typcn-plus"></i> Add Student
                                            </button>
                                        </div>
                                        
                                        <!-- Filters -->
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" id="studentSearch" placeholder="Search...">
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control" id="programmeFilter">
                                                    <option value="">All Programmes</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control" id="groupFilter">
                                                    <option value="">All Groups</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-control" id="roleFilter">
                                                    <option value="">All Roles</option>
                                                    <option value="user">User</option>
                                                    <option value="rep">Rep</option>
                                                    <option value="ta">TA</option>
                                                    <option value="lec">Lecturer</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button class="btn btn-primary" onclick="loadStudents()">Filter</button>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Programme</th>
                                                        <th>Group</th>
                                                        <th>Role</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentsTableBody">
                                                    <tr><td colspan="8" class="text-center">Loading...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add more sections similarly -->
                        </div>
                    </div>
                </div>

                <!-- Include Footer -->
                <?php include 'includes/footer.php'; ?>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="../../vendors/js/vendor.bundle.base.js"></script>
    <script src="../../vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../../vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../../vendors/chart.js/Chart.min.js"></script>
    <script src="../../js/off-canvas.js"></script>
    <script src="../../js/hoverable-collapse.js"></script>
    <script src="../../js/template.js"></script>
    <script src="../../js/settings.js"></script>
    
    <script>
        let currentChart = null;

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadRecentActivity();
            loadAttendanceChart();
            loadFilters();
        });

        // Load statistics
        function loadStats() {
            fetch('../../backend/stats/dashboard-stats.php')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayStats(data.data);
                    }
                });
        }

        function displayStats(stats) {
            const statsHtml = `
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card stat-card bg-primary text-white" onclick="loadSection('students')">
                        <div class="card-body">
                            <h6>Total Students</h6>
                            <h2>${stats.total_students || 0}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card stat-card bg-success text-white" onclick="loadSection('courses')">
                        <div class="card-body">
                            <h6>Total Courses</h6>
                            <h2>${stats.total_courses || 0}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card stat-card bg-info text-white" onclick="loadSection('sessions')">
                        <div class="card-body">
                            <h6>Active Sessions</h6>
                            <h2>${stats.active_sessions || 0}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card stat-card bg-warning text-white" onclick="loadSection('attendance')">
                        <div class="card-body">
                            <h6>Today's Attendance</h6>
                            <h2>${stats.today_attendance || 0}</h2>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('statsContainer').innerHTML = statsHtml;
        }

        // Load recent activity
        function loadRecentActivity() {
            fetch('../../backend/attendance/get-recent-activity.php')
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if (data.length === 0) {
                        html = '<tr><td colspan="4" class="text-center">No recent activity</td></tr>';
                    } else {
                        data.forEach(item => {
                            html += `<tr>
                                <td>${item.time}</td>
                                <td>${item.student_name}</td>
                                <td>${item.session_code}</td>
                                <td>${item.course_name || 'N/A'}</td>
                            </tr>`;
                        });
                    }
                    document.getElementById('recentActivityBody').innerHTML = html;
                });
        }

        // Load attendance chart
        function loadAttendanceChart() {
            fetch('../../backend/stats/last-7-days.php')
                .then(res => res.json())
                .then(data => {
                    const ctx = document.getElementById('attendanceChart').getContext('2d');
                    if (currentChart) currentChart.destroy();
                    
                    currentChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(d => d.date),
                            datasets: [{
                                label: 'Attendance',
                                data: data.map(d => d.count),
                                borderColor: '#4f6ef7',
                                backgroundColor: 'rgba(79, 110, 247, 0.1)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true
                        }
                    });
                });
        }

        // Load students
        function loadStudents() {
            const search = document.getElementById('studentSearch')?.value || '';
            const programme = document.getElementById('programmeFilter')?.value || '';
            const group = document.getElementById('groupFilter')?.value || '';
            const role = document.getElementById('roleFilter')?.value || '';
            
            let url = `../../backend/students/get-all-students.php?`;
            if (search) url += `&search=${search}`;
            if (programme) url += `&programme=${programme}`;
            if (group) url += `&group=${group}`;
            if (role) url += `&role=${role}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayStudents(data.data);
                    }
                });
        }

        function displayStudents(students) {
            let html = '';
            if (students.length === 0) {
                html = '<tr><td colspan="8" class="text-center">No students found</td></tr>';
            } else {
                students.forEach(s => {
                    const roleClass = {
                        'admin': 'danger',
                        'rep': 'warning',
                        'ta': 'info',
                        'lec': 'primary'
                    }[s.roles] || 'secondary';
                    
                    html += `<tr>
                        <td>${s.student_id}</td>
                        <td>${s.student_name}</td>
                        <td>${s.student_mail}</td>
                        <td>${s.programme_name || s.programme}</td>
                        <td>${s.group_name || 'N/A'}</td>
                        <td><span class="badge badge-${roleClass}">${s.roles}</span></td>
                        <td><span class="badge badge-${s.active == 1 ? 'success' : 'danger'}">${s.active == 1 ? 'Active' : 'Inactive'}</span></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editStudent('${s.student_id}')">
                                <i class="typcn typcn-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteStudent('${s.student_id}')">
                                <i class="typcn typcn-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('studentsTableBody').innerHTML = html;
        }

        // Load filter dropdowns
        function loadFilters() {
            // Load programmes
            fetch('../../backend/programmes/get-programmes.php')
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">All Programmes</option>';
                    if (data.status === 'success') {
                        data.data.forEach(p => {
                            options += `<option value="${p.programme_code}">${p.programme_name}</option>`;
                        });
                    }
                    document.getElementById('programmeFilter').innerHTML = options;
                });

            // Load groups
            fetch('../../backend/groups/get-groups-dropdown.php')
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">All Groups</option>';
                    if (data.status === 'success') {
                        data.data.forEach(g => {
                            options += `<option value="${g.group_id}">${g.group_name}</option>`;
                        });
                    }
                    document.getElementById('groupFilter').innerHTML = options;
                });
        }

        // Section navigation
        function loadSection(section) {
            document.querySelectorAll('.content-section').forEach(el => {
                el.classList.remove('active');
            });
            document.getElementById(section + '-section').classList.add('active');
            
            // Load section data
            switch(section) {
                case 'students':
                    loadStudents();
                    break;
            }
        }

        // Refresh all data
        function refreshAllData() {
            loadStats();
            loadRecentActivity();
            loadAttendanceChart();
        }

        // Student actions
        function showAddStudentModal() {
            $('#addStudentModal').modal('show');
        }

        function editStudent(id) {
            fetch(`../../backend/students/get-student.php?student_id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Populate and show edit modal
                        $('#editStudentModal').modal('show');
                    }
                });
        }

        function deleteStudent(id) {
            if (confirm('Delete this student?')) {
                fetch('../../backend/students/delete-student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ student_id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadStudents();
                    }
                });
            }
        }
    </script>
</body>
</html>