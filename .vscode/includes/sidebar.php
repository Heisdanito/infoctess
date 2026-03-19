<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="#" onclick="loadSection('dashboard')">
                <i class="typcn typcn-device-desktop menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#users-menu">
                <i class="typcn typcn-group menu-icon"></i>
                <span class="menu-title">Users</span>
                <i class="typcn typcn-chevron-right menu-arrow"></i>
            </a>
            <div class="collapse" id="users-menu">
                <ul class="nav flex-column sub-menu">
                    <li><a class="nav-link" href="#" onclick="loadSection('students')">Students</a></li>
                    <li><a class="nav-link" href="#" onclick="loadSection('admins')">Admins</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#academic-menu">
                <i class="typcn typcn-book menu-icon"></i>
                <span class="menu-title">Academic</span>
                <i class="typcn typcn-chevron-right menu-arrow"></i>
            </a>
            <div class="collapse" id="academic-menu">
                <ul class="nav flex-column sub-menu">
                    <li><a class="nav-link" href="#" onclick="loadSection('programmes')">Programmes</a></li>
                    <li><a class="nav-link" href="#" onclick="loadSection('courses')">Courses</a></li>
                    <li><a class="nav-link" href="#" onclick="loadSection('groups')">Groups</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#sessions-menu">
                <i class="typcn typcn-calendar menu-icon"></i>
                <span class="menu-title">Sessions</span>
                <i class="typcn typcn-chevron-right menu-arrow"></i>
            </a>
            <div class="collapse" id="sessions-menu">
                <ul class="nav flex-column sub-menu">
                    <li><a class="nav-link" href="#" onclick="loadSection('active-sessions')">Active</a></li>
                    <li><a class="nav-link" href="#" onclick="loadSection('all-sessions')">All Sessions</a></li>
                    <li><a class="nav-link" href="#" onclick="loadSection('create-session')">Create New</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../auth/logout.php">
                <i class="typcn typcn-power menu-icon"></i>
                <span class="menu-title">Logout</span>
            </a>
        </li>
    </ul>
</nav>