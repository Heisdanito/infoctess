let lineChart, doughnutChart;

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadActiveSessions();
    loadInactiveSessions();
    loadStudents();
    loadCourses();
    loadGroups();
    loadCharts();
    loadSessionFilter();
});

function loadStats() {
    fetch('../backend/stats/get-configure-stats.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('activeSessionsCount').textContent = data.active_sessions || 0;
            document.getElementById('presentToday').textContent = data.present_today || 0;
            document.getElementById('totalStudents').textContent = data.total_students || 0;
            document.getElementById('totalCourses').textContent = data.total_courses || 0;
        }).catch(e => console.log("data return is not an object "));
}

function loadActiveSessions() {
    fetch('../backend/sessions/get-active-sessions.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No active sessions</td></tr>';
            } else {
                data.forEach(session => {
                    html += `<tr>
                        <td><span class="badge badge-primary">${session.session_code}</span></td>
                        <td>${session.course_id || 'N/A'}</td>
                        <td>Group ${session.group_id}</td>
                        <td>${session.created_at}</td>
                        <td>${session.expire_at || 'Not set'}</td>
                        <td><span class="badge badge-info">${session.scan_count || 0}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="deactivateSession(${session.id})">
                                <i class="typcn typcn-power"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('activeSessionsBody').innerHTML = html;
        }).catch(e => console.log("data return is not an object "));
}

function loadInactiveSessions() {
    fetch('../backend/sessions/get-inactive-sessions.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No inactive sessions</td></tr>';
            } else {
                data.forEach(session => {
                    html += `<tr>
                        <td>${session.session_code}</td>
                        <td>${session.course_id || 'N/A'}</td>
                        <td>Group ${session.group_id}</td>
                        <td>${session.created_at}</td>
                        <td>${session.expire_at || 'N/A'}</td>
                        <td>${session.scan_count || 0}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="reactivateSession(${session.id})">
                                <i class="typcn typcn-refresh"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('inactiveSessionsBody').innerHTML = html;
        }).catch(e => console.log("data return is not an object "));
}

function loadStudents() {
    fetch('../backend/students/get-attendance-summary.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No students found</td></tr>';
            } else {
                data.forEach(student => {
                    const total = student.present_count + student.absent_count;
                    const percentage = total > 0 ? Math.round((student.present_count / total) * 100) : 0;
                    html += `<tr>
                        <td>${student.student_id}</td>
                        <td>${student.student_name}</td>
                        <td>${student.programme || 'N/A'}</td>
                        <td>Group ${student.group_id || 'N/A'}</td>
                        <td class="text-success">${student.present_count || 0}</td>
                        <td class="text-danger">${student.absent_count || 0}</td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar bg-${percentage >= 75 ? 'success' : (percentage >= 50 ? 'warning' : 'danger')}" 
                                    style="width: ${percentage}%">${percentage}%</div>
                            </div>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('studentsBody').innerHTML = html;
        }).catch(e => console.log("data return is not an object "));
}

function loadCourses() {
    fetch('../backend/courses/get-courses.php')
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Select Course</option>';
            data.forEach(course => {
                options += `<option value="${course.course_id}">${course.course_name}</option>`;
            });
            document.getElementById('courseSelect').innerHTML = options;
        }).catch(e => console.log("data return is not an object "));
}

function loadGroups() {
    fetch('../backend/groups/get-groups.php')
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Select Group</option>';
            data.forEach(group => {
                options += `<option value="${group.group_id}">${group.group_name}</option>`;
            });
            document.getElementById('groupSelect').innerHTML = options;
        }).catch(e => console.log("data return is not an object "));
}

function loadSessionFilter() {
    fetch('../backend/sessions/get-all-sessions.php')
        .then(res => res.json()).catch(e => console.log("data return is not an object "))
        .then(data => {
            let options = '<option value="">All Sessions</option>';
            data.forEach(session => {
                options += `<option value="${session.session_code}">${session.session_code}</option>`;
            });
            document.getElementById('sessionFilter').innerHTML = options;
        }).catch(e => console.log("data return is not an object "));
}

function loadCharts() {
    // Line Chart - Last 5 Sessions
    fetch('../backend/sessions/get-last-5-sessions.php')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('lineChart').getContext('2d');
            if (lineChart) lineChart.destroy();
            
            lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(s => s.session_code),
                    datasets: [{
                        label: 'Attendance',
                        data: data.map(s => s.attendance_count),
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

    // Doughnut Chart - Present vs Absent
    fetch('../backend/stats/get-attendance-stats.php')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('doughnutChart').getContext('2d');
            if (doughnutChart) doughnutChart.destroy();
            
            doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [data.present || 0, data.absent || 0],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        });
}

function createSession() {
    const data = {
        course_id: document.getElementById('courseSelect').value,
        group_id: document.getElementById('groupSelect').value,
        duration: document.getElementById('duration').value
    };

    fetch('../backend/sessions/create-session.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            $('#createSessionModal').modal('hide');
            loadActiveSessions();
            alert('Session created! QR Code: ' + data.qr_code);
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function deactivateSession(id) {
    if (confirm('Deactivate this session?')) {
        fetch('../backend/sessions/deactivate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                loadActiveSessions();
                loadInactiveSessions();
                loadStats();
            }
        });
    }
}

function reactivateSession(id) {
    if (confirm('Reactivate this session?')) {
        fetch('../backend/sessions/reactivate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                loadActiveSessions();
                loadInactiveSessions();
            }
        });
    }
}

// Search functionality
document.getElementById('studentSearch').addEventListener('keyup', function() {
    const search = this.value.toLowerCase();
    const rows = document.querySelectorAll('#studentsBody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});

document.getElementById('sessionFilter').addEventListener('change', function() {
    const session = this.value;
    if (session) {
        fetch(`../backend/attendance/get-by-session.php?session_code=${session}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(student => {
                    html += `<tr>
                        <td>${student.student_id}</td>
                        <td>${student.student_name}</td>
                        <td>${student.programme || 'N/A'}</td>
                        <td>Group ${student.group_id || 'N/A'}</td>
                        <td class="text-success">${student.present_count || 0}</td>
                        <td class="text-danger">${student.absent_count || 0}</td>
                        <td>${student.percentage || 0}%</td>
                    </tr>`;
                });
                document.getElementById('studentsBody').innerHTML = html || '<tr><td colspan="7">No records</td></tr>';
            });
    } else {
        loadStudents();
    }
});