   // Admin authentication state
    let isAdminAuthenticated = false;
    let currentAdmin = null;

    // Check if admin is already logged in (check session)
    function checkAdminSession() {
        fetch('../../backend/auth/check-admin-session.php')
            .then(res => res.json())
            .then(response => {
                if (response.authenticated && response.role === 'admin') {
                    isAdminAuthenticated = true;
                    currentAdmin = response.user;
                    showAdminContent();
                    loadAllAdminData();
                } else {
                    showLoginForm();
                }
            })
            .catch(() => {
                showLoginForm();
            });
    }

    // Show login form, hide admin content
    function showLoginForm() {
        document.getElementById('loginSection').classList.remove('hidden');
        document.getElementById('adminContent').classList.add('hidden');
    }

    // Show admin content, hide login form
    function showAdminContent() {
        document.getElementById('loginSection').classList.add('hidden');
        document.getElementById('adminContent').classList.remove('hidden');
        
        // Update admin name displays
        if (currentAdmin) {
            document.getElementById('adminNameDisplay').textContent = currentAdmin.name || 'Admin';
            document.getElementById('sidebarAdminName').textContent = currentAdmin.name || 'Admin User';
            document.getElementById('welcomeAdminName').textContent = currentAdmin.name || 'Admin';
        }
    }

    // Handle login form submission
    document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('adminUsername').value;
        const password = document.getElementById('adminPassword').value;
        
        // Show loading
        document.getElementById('loginSpinner').classList.remove('hidden');
        document.getElementById('loginBtn').disabled = true;
        
        // Attempt login
        fetch('../../backend/auth/admin-login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        })
        .then(res => res.json())
        .then(response => {
            document.getElementById('loginSpinner').classList.add('hidden');
            document.getElementById('loginBtn').disabled = false;
            
            if (response.status === 'success' && response.role === 'admin') {
                isAdminAuthenticated = true;
                currentAdmin = response.user;
                showAdminContent();
                loadAllAdminData();
            } else {
                document.getElementById('loginError').textContent = response.message || 'Invalid credentials';
                document.getElementById('loginError').classList.remove('hidden');
            }
        })
        .catch(error => {
            document.getElementById('loginSpinner').classList.add('hidden');
            document.getElementById('loginBtn').disabled = false;
            document.getElementById('loginError').textContent = 'Connection error';
            document.getElementById('loginError').classList.remove('hidden');
        });
    });

    // Logout function
    function logoutAdmin() {
        fetch('../../backend/auth/admin-logout.php')
            .then(() => {
                isAdminAuthenticated = false;
                currentAdmin = null;
                showLoginForm();
            });
    }

    // Show different sections
    function showSection(section) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Show selected section
        const sectionElement = document.getElementById(section + '-section');
        if (sectionElement) {
            sectionElement.classList.remove('hidden');
        }
        
        // Load section data
        switch(section) {
            case 'dashboard':
                loadDashboardData();
                break;
            case 'students':
                loadStudentsManagement();
                break;
            case 'programmes':
                loadProgrammesManagement();
                break;
            case 'courses':
                loadCoursesManagement();
                break;
            case 'sessions':
            case 'active-sessions':
                loadSessions('active');
                break;
            case 'all-sessions':
                loadSessions('all');
                break;
            case 'create-session':
                loadCourseAndGroupOptions();
                break;
        }
    }

    // Load all admin data initially
    function loadAllAdminData() {
        loadDashboardData();
        loadStudentsManagement();
        loadProgrammesManagement();
        loadCoursesManagement();
        loadCourseAndGroupOptions();
        loadFilterOptions();
    }

    // Load filter options for dropdowns
    function loadFilterOptions() {
        // Load programmes for filter
        fetch('../../backend/programmes/get-programmes.php')
            .then(res => res.json())
            .then(response => {
                let options = '<option value="">All Programmes</option>';
                if (response.status === 'success' && Array.isArray(response.data)) {
                    response.data.forEach(prog => {
                        options += `<option value="${prog.programme_code}">${prog.programme_name}</option>`;
                    });
                }
                document.getElementById('studentProgrammeFilter').innerHTML = options;
            })
            .catch(error => console.error('Error loading programmes:', error));

        // Load groups for filter
        fetch('../../backend/groups/get-groups-dropdown.php')
            .then(res => res.json())
            .then(response => {
                let options = '<option value="">All Groups</option>';
                if (response.status === 'success' && Array.isArray(response.data)) {
                    response.data.forEach(group => {
                        options += `<option value="${group.group_id}">${group.group_name}</option>`;
                    });
                }
                document.getElementById('studentGroupFilter').innerHTML = options;
            })
            .catch(error => console.error('Error loading groups:', error));
    }

    // Dashboard data
    function loadDashboardData() {
        fetch('../../backend/stats/dashboard-stats.php')
            .then(res => res.json())
            .then(response => {
                let stats = {};
                if (response.status === 'success' && response.data) {
                    stats = response.data;
                } else {
                    stats = response;
                }
                
                document.getElementById('totalStudents').textContent = stats.total_students || 0;
                document.getElementById('totalCourses').textContent = stats.total_courses || 0;
                document.getElementById('totalActiveSessions').textContent = stats.active_sessions || 0;
                document.getElementById('todayAttendance').textContent = stats.today_attendance || 0;
                
                // Load recent activity
                let activityHtml = '';
                if (stats.recent_activity && stats.recent_activity.length > 0) {
                    stats.recent_activity.forEach(act => {
                        activityHtml += `<tr>
                            <td>${act.time || act.created_at}</td>
                            <td>${act.user || act.student_name}</td>
                            <td>${act.action || 'Attendance'}</td>
                            <td>${act.details || act.course_name || ''}</td>
                        </tr>`;
                    });
                } else {
                    activityHtml = '<tr><td colspan="4" class="text-center">No recent activity</td></tr>';
                }
                document.getElementById('recentActivityBody').innerHTML = activityHtml;
            })
            .catch(error => {
                console.error('Error loading dashboard:', error);
                document.getElementById('recentActivityBody').innerHTML = 
                    '<tr><td colspan="4" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    // Students management
    function loadStudentsManagement() {
        const search = document.getElementById('studentSearchInput')?.value || '';
        const programme = document.getElementById('studentProgrammeFilter')?.value || '';
        const group = document.getElementById('studentGroupFilter')?.value || '';
        const role = document.getElementById('studentRoleFilter')?.value || '';
        
        let url = '../../backend/students/get-all-students.php?';
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (programme) url += `&programme=${programme}`;
        if (group) url += `&group=${group}`;
        if (role) url += `&role=${role}`;
        
        fetch(url)
            .then(res => res.json())
            .then(response => {
                let html = '';
                // Check if response has data property and it's an array
                const students = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                if (students.length === 0) {
                    html = '<tr><td colspan="8" class="text-center">No students found</td></tr>';
                } else {
                    students.forEach(student => {
                        const roleClass = student.roles === 'admin' ? 'danger' : 
                                        student.roles === 'rep' ? 'warning' :
                                        student.roles === 'ta' ? 'info' : 
                                        student.roles === 'lec' ? 'primary' : 'secondary';
                        
                        html += `<tr>
                            <td>${student.student_id || ''}</td>
                            <td>${student.student_name || ''}</td>
                            <td>${student.student_mail || ''}</td>
                            <td>${student.programme_name || student.programme || ''}</td>
                            <td>${student.group_name ? student.group_name : (student.group_id ? 'Group ' + student.group_id : 'N/A')}</td>
                            <td><span class="badge badge-${roleClass}">${student.roles || 'user'}</span></td>
                            <td><span class="badge badge-${student.active == 1 ? 'success' : 'danger'}">${student.active == 1 ? 'Active' : 'Inactive'}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info action-btn" onclick="editStudent('${student.student_id}')">
                                    <i class="typcn typcn-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteStudent('${student.student_id}')">
                                    <i class="typcn typcn-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('studentsManagementBody').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading students:', error);
                document.getElementById('studentsManagementBody').innerHTML = 
                    '<tr><td colspan="8" class="text-center text-danger">Error loading students</td></tr>';
            });
    }

    // Programmes Management (NEW)
    function loadProgrammesManagement() {
        // First, check if the programmes section exists, if not create it
        if (!document.getElementById('programmes-section')) {
            createProgrammesSection();
        }
        
        fetch('../../backend/programmes/get-programmes.php')
            .then(res => res.json())
            .then(response => {
                let html = '';
                // Handle the response format {status: "success", data: [...]}
                const programmes = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                if (programmes.length === 0) {
                    html = '<tr><td colspan="5" class="text-center">No programmes found</td></tr>';
                } else {
                    programmes.forEach(prog => {
                        html += `<tr>
                            <td>${prog.programme_code || ''}</td>
                            <td>${prog.programme_name || ''}</td>
                            <td>${prog.student_count || prog.num_of_stu || 0}</td>
                            <td>${prog.course_count || 0}</td>
                            <td>${prog.year || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-info action-btn" onclick="editProgramme('${prog.programme_code}')">
                                    <i class="typcn typcn-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteProgramme('${prog.programme_code}')">
                                    <i class="typcn typcn-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                }
                
                // Update the table body if it exists
                const tbody = document.querySelector('#programmes-section tbody');
                if (tbody) {
                    tbody.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error loading programmes:', error);
                const tbody = document.querySelector('#programmes-section tbody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading programmes</td></tr>';
                }
            });
    }

    // Create programmes section if it doesn't exist
    function createProgrammesSection() {
        const contentWrapper = document.querySelector('.content-wrapper');
        if (!contentWrapper) return;
        
        const section = document.createElement('div');
        section.id = 'programmes-section';
        section.className = 'content-section hidden';
        section.innerHTML = `
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0 font-weight-bold">Programme Management</h3>
                    <p class="lastLog">Manage academic programmes</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-primary" onclick="showAddProgrammeModal()">
                        <i class="typcn typcn-plus"></i> Add Programme
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Programme Code</th>
                                            <th>Programme Name</th>
                                            <th>Students</th>
                                            <th>Courses</th>
                                            <th>Year</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="6" class="text-center">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Insert after students section or at the end
        const studentsSection = document.getElementById('students-section');
        if (studentsSection) {
            studentsSection.insertAdjacentElement('afterend', section);
        } else {
            contentWrapper.appendChild(section);
        }
    }

    // Courses management
    function loadCoursesManagement() {
        fetch('../../backend/courses/get-courses.php')
            .then(res => res.json())
            .then(response => {
                let html = '';
                const courses = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                if (courses.length === 0) {
                    html = '<tr><td colspan="6" class="text-center">No courses found</td></tr>';
                } else {
                    courses.forEach(course => {
                        html += `<tr>
                            <td>${course.course_id || ''}</td>
                            <td>${course.course_name || ''}</td>
                            <td>${course.programme_name || course.programme_id || ''}</td>
                            <td>${course.group_name ? course.group_name : (course.group_id ? 'Group ' + course.group_id : 'N/A')}</td>
                            <td>${course.created_at || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-info action-btn" onclick="editCourse('${course.course_id}')">
                                    <i class="typcn typcn-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteCourse('${course.course_id}')">
                                    <i class="typcn typcn-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('coursesManagementBody').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading courses:', error);
                document.getElementById('coursesManagementBody').innerHTML = 
                    '<tr><td colspan="6" class="text-center text-danger">Error loading courses</td></tr>';
            });
    }

    // Sessions management
    function loadSessions(type) {
        let endpoint = '../../backend/sessions/get-all-sessions.php';
        if (type === 'active') {
            endpoint = '../../backend/sessions/get-active-sessions.php';
        } else if (type === 'inactive') {
            endpoint = '../../backend/sessions/get-inactive-sessions.php';
        }
        
        fetch(endpoint)
            .then(res => res.json())
            .then(response => {
                let html = '';
                const sessions = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                if (sessions.length === 0) {
                    html = '<tr><td colspan="9" class="text-center">No sessions found</td></tr>';
                } else {
                    sessions.forEach(session => {
                        const isActive = session.is_active == 1;
                        const isExpired = session.is_expired || false;
                        let statusText = 'Active';
                        let statusClass = 'success';
                        
                        if (!isActive) {
                            statusText = 'Inactive';
                            statusClass = 'secondary';
                        } else if (isExpired) {
                            statusText = 'Expired';
                            statusClass = 'warning';
                        }
                        
                        html += `<tr>
                            <td><span class="badge badge-primary">${session.session_code || ''}</span></td>
                            <td>${session.course_name || session.course_id || ''}</td>
                            <td>${session.group_name ? session.group_name : (session.group_id ? 'Group ' + session.group_id : 'N/A')}</td>
                            <td>${session.created_by_name || session.created_by || ''}</td>
                            <td>${session.created_at || ''}</td>
                            <td>${session.expire_at || 'N/A'}</td>
                            <td><span class="badge badge-${statusClass}">${statusText}</span></td>
                            <td><span class="badge badge-info">${session.scan_count || 0}</span></td>
                            <td>
                                ${isActive ? 
                                    `<button class="btn btn-sm btn-warning action-btn" onclick="deactivateSession(${session.id})">
                                        <i class="typcn typcn-power"></i>
                                    </button>` : 
                                    `<button class="btn btn-sm btn-success action-btn" onclick="reactivateSession(${session.id})">
                                        <i class="typcn typcn-refresh"></i>
                                    </button>`
                                }
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteSession(${session.id})">
                                    <i class="typcn typcn-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('sessionsManagementBody').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading sessions:', error);
                document.getElementById('sessionsManagementBody').innerHTML = 
                    '<tr><td colspan="9" class="text-center text-danger">Error loading sessions</td></tr>';
            });
    }

    // Load course and group options for forms
    function loadCourseAndGroupOptions() {
        // Load courses
        fetch('../../backend/courses/get-courses.php')
            .then(res => res.json())
            .then(response => {
                let options = '<option value="">Select Course</option>';
                const courses = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                courses.forEach(course => {
                    options += `<option value="${course.course_id}">${course.course_name}</option>`;
                });
                document.getElementById('adminCourseSelect').innerHTML = options;
            })
            .catch(error => console.error('Error loading courses:', error));
        
        // Load groups
        fetch('../../backend/groups/get-groups-dropdown.php')
            .then(res => res.json())
            .then(response => {
                let options = '<option value="">Select Group</option>';
                const groups = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                groups.forEach(group => {
                    options += `<option value="${group.group_id}">${group.group_name}</option>`;
                });
                document.getElementById('adminGroupSelect').innerHTML = options;
            })
            .catch(error => console.error('Error loading groups:', error));
    }

    // Load programme options for modals
    function loadProgrammeOptions() {
        fetch('../../backend/programmes/get-programmes.php')
            .then(res => res.json())
            .then(response => {
                let options = '<option value="">Select Programme</option>';
                const programmes = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                programmes.forEach(prog => {
                    options += `<option value="${prog.programme_code}">${prog.programme_name}</option>`;
                });
                
                const newStudentProg = document.getElementById('newStudentProgramme');
                const editStudentProg = document.getElementById('editStudentProgramme');
                
                if (newStudentProg) newStudentProg.innerHTML = options;
                if (editStudentProg) editStudentProg.innerHTML = options;
                
                // Also load groups after programmes
                loadGroupOptions();
            })
            .catch(error => console.error('Error loading programmes:', error));
    }

    // Load group options for modals
    function loadGroupOptions() {
        fetch('../../backend/groups/get-groups-dropdown.php')
            .then(res => res.json())
            .then(response => {
                let options = '<option value="">Select Group</option>';
                const groups = (response.status === 'success' && Array.isArray(response.data)) 
                    ? response.data 
                    : (Array.isArray(response) ? response : []);
                
                groups.forEach(group => {
                    options += `<option value="${group.group_id}">${group.group_name}</option>`;
                });
                
                const newStudentGroup = document.getElementById('newStudentGroup');
                const editStudentGroup = document.getElementById('editStudentGroup');
                
                if (newStudentGroup) newStudentGroup.innerHTML = options;
                if (editStudentGroup) editStudentGroup.innerHTML = options;
            })
            .catch(error => console.error('Error loading groups:', error));
    }

    // Search functionality for students
    document.getElementById('studentSearchInput')?.addEventListener('keyup', function() {
        loadStudentsManagement();
    });

    // Filter change events
    document.getElementById('studentProgrammeFilter')?.addEventListener('change', function() {
        loadStudentsManagement();
    });

    document.getElementById('studentGroupFilter')?.addEventListener('change', function() {
        loadStudentsManagement();
    });

    document.getElementById('studentRoleFilter')?.addEventListener('change', function() {
        loadStudentsManagement();
    });

    // Show add student modal
    function showAddStudentModal() {
        $('#addStudentModal').modal('show');
    }

    // Add student
    function addStudent() {
        const data = {
            action: 'add_student',
            student_id: document.getElementById('newStudentId').value,
            student_name: document.getElementById('newStudentName').value,
            student_mail: document.getElementById('newStudentEmail').value,
            programme: document.getElementById('newStudentProgramme').value,
            group_id: document.getElementById('newStudentGroup').value,
            roles: document.getElementById('newStudentRole').value
        };
        
        fetch('../../backend/config/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                $('#addStudentModal').modal('hide');
                loadStudentsManagement();
                alert('Student added successfully');
            } else {
                alert('Error: ' + response.message);
            }
        })
        .catch(error => {
            alert('Connection error');
        });
    }

    // Edit student
    function editStudent(studentId) {
        fetch(`../../backend/students/get-student.php?student_id=${studentId}`)
            .then(res => res.json())
            .then(response => {
                const student = response.status === 'success' ? response.data : response;
                
                document.getElementById('editStudentId').value = student.student_id || '';
                document.getElementById('editStudentName').value = student.student_name || '';
                document.getElementById('editStudentEmail').value = student.student_mail || '';
                document.getElementById('editStudentProgramme').value = student.programme || '';
                document.getElementById('editStudentGroup').value = student.group_id || '';
                document.getElementById('editStudentRole').value = student.roles || 'user';
                document.getElementById('editStudentStatus').value = student.active || '1';
                
                $('#editStudentModal').modal('show');
            })
            .catch(error => {
                alert('Error loading student data');
            });
    }

    // Update student
    function updateStudent() {
        const data = {
            action: 'edit_student',
            student_id: document.getElementById('editStudentId').value,
            student_name: document.getElementById('editStudentName').value,
            student_mail: document.getElementById('editStudentEmail').value,
            programme: document.getElementById('editStudentProgramme').value,
            group_id: document.getElementById('editStudentGroup').value,
            roles: document.getElementById('editStudentRole').value,
            active: document.getElementById('editStudentStatus').value
        };
        
        fetch('../../backend/config/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                $('#editStudentModal').modal('hide');
                loadStudentsManagement();
                alert('Student updated successfully');
            } else {
                alert('Error: ' + response.message);
            }
        });
    }

    // Delete student
    function deleteStudent(studentId) {
        if (confirm('Are you sure you want to delete this student?')) {
            fetch('../../backend/config/actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_student',
                    student_id: studentId
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    loadStudentsManagement();
                    alert('Student deleted');
                } else {
                    alert('Error: ' + response.message);
                }
            });
        }
    }

    // Programme functions
    function showAddProgrammeModal() {
        alert('Add programme modal - to be implemented');
    }

    function editProgramme(code) {
        alert('Edit programme: ' + code);
    }

    function deleteProgramme(code) {
        if (confirm('Delete this programme?')) {
            alert('Delete programme: ' + code);
        }
    }

    // Create session
    function adminCreateSession() {
        const data = {
            action: 'create_session',
            course_id: document.getElementById('adminCourseSelect').value,
            group_id: document.getElementById('adminGroupSelect').value,
            duration: document.getElementById('adminDuration').value,
            latitude: document.getElementById('adminLatitude').value,
            longitude: document.getElementById('adminLongitude').value
        };
        
        if (!data.course_id || !data.group_id) {
            alert('Please select course and group');
            return;
        }
        
        fetch('../../backend/config/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                alert('Session created! QR Code: ' + (response.data?.qrcode || ''));
                showSection('sessions');
            } else {
                alert('Error: ' + response.message);
            }
        })
        .catch(error => {
            alert('Connection error');
        });
    }

    // Deactivate session
    function deactivateSession(sessionId) {
        if (confirm('Deactivate this session?')) {
            fetch('../../backend/config/actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'deactivate_session',
                    session_id: sessionId
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    loadSessions('active');
                }
            });
        }
    }

    // Reactivate session
    function reactivateSession(sessionId) {
        if (confirm('Reactivate this session?')) {
            fetch('../../backend/config/actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'reactivate_session',
                    session_id: sessionId
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    loadSessions('inactive');
                }
            });
        }
    }

    // Delete session
    function deleteSession(sessionId) {
        if (confirm('Permanently delete this session?')) {
            fetch('../../backend/config/actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_session',
                    session_id: sessionId
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    loadSessions('all');
                }
            });
        }
    }

    // Delete course
    function deleteCourse(courseId) {
        if (confirm('Delete this course?')) {
            fetch('../../backend/config/actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_course',
                    course_id: courseId
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    loadCoursesManagement();
                }
            });
        }
    }

    // Edit course
    function editCourse(courseId) {
        alert('Edit course: ' + courseId);
    }

    // Refresh all data
    function refreshAllData() {
        loadDashboardData();
        alert('Data refreshed');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        checkAdminSession();
    });
