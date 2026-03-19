<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="index.php">
            <img src="../images/logo.png" alt="logo"/>
        </a>
        <a class="navbar-brand brand-logo-mini" href="index.php">
            <img src="../images/logo.png" alt="logo"/>
        </a>
        <button class="navbar-toggler navbar-toggler align-self-center d-none d-lg-flex" type="button" data-toggle="minimize">
            <span class="typcn typcn-th-menu"></span>
        </button>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    <i class="typcn typcn-user-outline"></i>
                    <span class="nav-profile-name"><?php echo htmlspecialchars($admin_name); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="profile.php">
                        <i class="typcn typcn-user"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="../auth/logout.php">
                        <i class="typcn typcn-power"></i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>