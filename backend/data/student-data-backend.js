let progressCircle = null;
let studentId = null;
let groupId = null;
let allCourses = [];

// Initialize progress circle
const circleContainer = document.getElementById('attendanceProgress');
if (circleContainer) {
    progressCircle = new ProgressBar.Circle(circleContainer, {
        color: '#4f6ef7',
        trailColor: '#e9ecef',
        trailWidth: 15,
        duration: 1500,
        easing: 'bounce',
        strokeWidth: 10,
        text: {
            autoStyleContainer: false
            
        },
        step: function(state, circle) {
            circle.setText((circle.value() * 100).toFixed(0) + '%');
        }
    });
}

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    // Load student info first
    loadStudentInfo();
    
    // Course search functionality
    document.getElementById('courseSearch').addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        const items = document.querySelectorAll('.course-item');
        items.forEach(item => {
            if (item.textContent.toLowerCase().includes(search)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Load student info from backend/user.php
function loadStudentInfo() {
    fetch('../backend/data/user.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'fetched') {
                studentId = data.stu_id;
                groupId = data.group_id;
                
                document.getElementById('studentName').textContent = data.username || 'Student';
                document.getElementById('sidebarStudentName').textContent = data.username || 'Student';
                
                // Format last login date
                const lastLogin = data.lastlogin ? new Date(data.lastlogin) : new Date();
                const formattedDate = lastLogin.toLocaleDateString() + ' ' + lastLogin.toLocaleTimeString();
                document.getElementById('lastLogin').textContent = 'Last login: ' + formattedDate;
                
                // After getting student info, load attendance data
                loadAttendanceStats();
            } else {
                console.error('Failed to load student data:', data);
                // Redirect to login if not authenticated
                window.location.href = '../auth/login.html';
            }
        })
        .catch(error => {
            console.error('Error loading student info:', error);
        });
}

// Load courses and attendance from backend/student-att.php
function loadAttendanceStats() {
    if (!studentId) return;
    
    fetch('../backend/attendance/student-att.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Update overall stats
                document.getElementById('totalClasses').textContent = data.overall.total_classes;
                document.getElementById('attendedClasses').textContent = data.overall.attended;
                document.getElementById('missedClasses').textContent = data.overall.missed;
                
                const percent = data.overall.percentage;
                document.getElementById('attendancePercent').textContent = percent + '%';
                
                // Update remarks
                let remarks = 'Good';
                let badgeClass = 'success';
                if (percent < 75) {
                    remarks = 'Warning';
                    badgeClass = 'warning';
                } else if (percent < 50) {
                    remarks = 'Critical';
                    badgeClass = 'danger';
                }
                document.getElementById('attendanceRemarks').textContent = remarks;
                document.getElementById('attendanceRemarks').className = 'badge badge-' + badgeClass;
                
                // Update progress circle
                if (progressCircle) {
                    progressCircle.animate(percent / 100);
                }
                
                // Update sidebar stats
                document.getElementById('totalClassesSide').textContent = 'Total Classes: ' + data.overall.total_classes;
                document.getElementById('attendedSide').textContent = 'Attended: ' + data.overall.attended;
                document.getElementById('percentageSide').textContent = 'Percentage: ' + percent + '%';
                
                // Load courses table with the courses data from response
                if (data.courses && data.courses.length > 0) {
                    loadCoursesTable(data.courses);
                    // Update enrolled courses count
                    document.getElementById('enrolledCourses').textContent = data.courses.length;
                } else {
                    // If no courses in response, try to load courses separately
                    loadCourses();
                }
                
                // Load group progress for active sessions
                loadGroupProgress();
                
                // Load recent activity if available
                if (data.recent) {
                    loadRecentActivity(data.recent);
                }
            } else {
                console.error('Failed to load attendance:', data);
            }
        })
        .catch(error => {
            console.error('Error loading attendance:', error);
        });
}

// Load courses separately if needed
function loadCourses() {
    fetch('../backend/courses/get-student-courses.php')
        .then(res => res.json())
        .then(response => {
            const courses = response.data || [];
            if (courses.length > 0) {
                // Transform courses to match the format expected by loadCoursesTable
                const formattedCourses = courses.map(course => ({
                    course_id: course.course_id,
                    course_name: course.course_name,
                    attended: 0,
                    missed: 0,
                    total_sessions: 0,
                    percentage: 0
                }));
                loadCoursesTable(formattedCourses);
                document.getElementById('enrolledCourses').textContent = formattedCourses.length;
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
        });
}

// Load group progress from backend/progress.php
function loadGroupProgress() {
    if (!groupId) return;
    
    fetch('../backend/attendance/progress.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('activeSessions').textContent = data.total_members || 0;
                // Streak days would need separate endpoint
            }
        })
        .catch(error => {
            console.error('Error loading group progress:', error);
        });
}

// Load courses table
function loadCoursesTable(courses) {
    let html = '';
    if (!courses || courses.length === 0) {
        html = '<tr><td colspan="7" class="text-center">No course data available</td></tr>';
    } else {
        courses.forEach(course => {
            const percent = course.percentage || 0;
            let statusClass = 'success';
            let statusText = 'Good';
            
            if (percent < 75) {
                statusClass = 'warning';
                statusText = 'Average';
            }
            if (percent < 50) {
                statusClass = 'danger';
                statusText = 'Poor';
            }
            
            html += `<tr>
                <td><span class="badge badge-primary">${course.course_id || ''}</span></td>
                <td>${course.course_name || ''}</td>
                <td class="text-success">${course.attended || 0}</td>
                <td class="text-danger">${course.missed || 0}</td>
                <td>${course.total_sessions || 0}</td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-${statusClass}" style="width: ${percent}%">${percent}%</div>
                    </div>
                </td>
                <td><span class="badge badge-${statusClass}">${statusText}</span></td>
            </tr>`;
        });
    }
    document.getElementById('coursesTableBody').innerHTML = html;
    
    // Update course list in sidebar
    updateCourseSidebar(courses);
}

// Load recent activity
function loadRecentActivity(recent) {
    let html = '';
    if (!recent || recent.length === 0) {
        html = '<p class="mb-1 text-muted">• No recent activity</p>';
    } else {
        recent.slice(0, 5).forEach(act => {
            html += `<p class="mb-1">• ${act.course_name || act.course_id} - ${act.date || act.time || ''}</p>`;
        });
    }
    document.getElementById('recentActivity').innerHTML = html;
}
loadCourses()

// Update course list in sidebar
function updateCourseSidebar(courses) {
    let courseHtml = '';
    if (!courses || courses.length === 0) {
        courseHtml = '<li class="nav-item"><span class="nav-link text-muted">No courses enrolled</span></li>';
    } else {
        courses.forEach(course => {
            const percent = course.percentage || 0;
            courseHtml += `<li class="nav-item course-item" onclick="filterByCourse('${course.course_id}')">
                <a class="nav-link" href="#" style="padding: 10px 15px;">
                    ${course.course_id} - <span class="float-right">${percent}%</span>
                </a>
            </li>`;
        });
    }
    document.getElementById('courseList').innerHTML = courseHtml;
    
    // Update modal course select
    let selectHtml = '<option value="">Select course</option>';
    courses.forEach(course => {
        selectHtml += `<option value="${course.course_id}">${course.course_name}</option>`;
    });
    document.getElementById('modalCourseSelect').innerHTML = selectHtml;
}

// Filter by course
function filterByCourse(courseId) {
    // Remove active class from all course items
    document.querySelectorAll('.course-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked item
    if (event) event.currentTarget.classList.add('active');
    
    // Filter table rows
    const rows = document.querySelectorAll('#coursesTableBody tr');
    rows.forEach(row => {
        if (row.cells[0] && row.cells[0].textContent.includes(courseId)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Close sidebar on mobile after selection
    if (window.innerWidth <= 768) {
        document.querySelector('.sidebar-offcanvas').classList.remove('active');
    }
}

// Submit manual attendance from main input
function submitManualAttendance() {
    const sessionCode = document.getElementById('manualSessionCode').value.trim();
    const feedback = document.getElementById('manualFeedback');
    
    if (!sessionCode) {
        feedback.innerHTML = '<span class="text-warning">⚠️ Please enter a session code</span>';
        return;
    }
    
    feedback.innerHTML = '<span class="text-info">⏳ Processing...</span>';
    
    // Get current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const data = {
                    session_code: sessionCode,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                fetch('../backend/attendance/mark-manual.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        feedback.innerHTML = '<span class="text-success">✅ Attendance marked successfully!</span>';
                        document.getElementById('manualSessionCode').value = '';
                        
                        // Refresh data after 2 seconds
                        setTimeout(() => {
                            refreshData();
                            feedback.innerHTML = '';
                        }, 2000);
                    } else {
                        feedback.innerHTML = '<span class="text-white">❌ ' + (response.message || 'Failed') + '</span>';
                    }
                })
                .catch(error => {
                    feedback.innerHTML = '<span class="text-white">❌ Connection error</span>';
                });
            },
            function(error) {
                feedback.innerHTML = '<span class="text-white">⚠️ Location required: ' + error.message + '</span>';
            }
        );
    } else {
        feedback.innerHTML = '<span class="text-white">⚠️ Geolocation not supported</span>';
    }
}

// Submit modal attendance
function submitModalAttendance() {
    const sessionCode = document.getElementById('modalSessionCode').value.trim();
    const courseId = document.getElementById('modalCourseSelect').value;
    const feedback = document.getElementById('modalFeedback');
    
    if (!sessionCode) {
        feedback.className = 'alert alert-danger';
        feedback.textContent = 'Please enter a session code';
        feedback.classList.remove('d-none');
        return;
    }
    
    feedback.className = 'alert alert-info';
    feedback.textContent = 'Processing...';
    feedback.classList.remove('d-none');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const data = {
                    session_code: sessionCode,
                    course_id: courseId,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                fetch('../backend/attendance/mark-manual.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        feedback.className = 'alert alert-success';
                        feedback.textContent = 'Attendance marked successfully!';
                        
                        setTimeout(() => {
                            $('#manualSessionModal').modal('hide');
                            refreshData();
                        }, 1500);
                    } else {
                        feedback.className = 'alert alert-danger';
                        feedback.textContent = response.message || 'Failed to mark attendance';
                    }
                })
                .catch(error => {
                    feedback.className = 'alert alert-danger';
                    feedback.textContent = 'Connection error';
                });
            },
            function(error) {
                feedback.className = 'alert alert-warning';
                feedback.textContent = 'Location required: ' + error.message;
            }
        );
    } else {
        feedback.className = 'alert alert-warning';
        feedback.textContent = 'Geolocation not supported';
    }
}

// Refresh all data
function refreshData() {
    loadAttendanceStats(); // This will reload everything including courses
}

// Auto-refresh every 30 seconds
setInterval(refreshData, 30000);