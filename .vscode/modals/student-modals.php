<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" class="form-control" id="newStudentId" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" id="newStudentName" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="newStudentEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Programme</label>
                        <select class="form-control" id="newStudentProgramme" required>
                            <option value="">Select Programme</option>
                            <?php
                            $prog_query = mysqli_query($conn, "SELECT programme_code, programme_name FROM programme");
                            while ($prog = mysqli_fetch_assoc($prog_query)) {
                                echo "<option value='{$prog['programme_code']}'>{$prog['programme_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Group</label>
                        <select class="form-control" id="newStudentGroup">
                            <option value="">Select Group</option>
                            <?php
                            $group_query = mysqli_query($conn, "SELECT group_id, group_name FROM `groups` WHERE status='active'");
                            while ($group = mysqli_fetch_assoc($group_query)) {
                                echo "<option value='{$group['group_id']}'>{$group['group_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" id="newStudentRole">
                            <option value="user">User</option>
                            <option value="rep">Rep</option>
                            <option value="ta">TA</option>
                            <option value="lec">Lecturer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addStudent()">Add Student</button>
            </div>
        </div>
    </div>
</div>