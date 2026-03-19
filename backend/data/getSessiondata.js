$(document).ready(function(){
    $("#showession").fadeOut()

    $("#loadSession").click(async () => {
        const url = "../backend/data/Load-session.php";
        setTimeout(async  () => {
            try {
                const response = await fetch(url);
                const res = await response.json(); // parse JSON

                console.log(res);
                // Append the HTML message to #loadin
                if(res.status === "failed"){
                    console.log("error")
                } else if(res.status === "success"){
                    // Example: append courses as a list

                    $("#loadin").prepend(
                                    `<div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Create Session (note: session expires at every 2hrs)</h5>
                                            <button type="button" class="close" data-dismiss="modal" id="CloseM">
                                            <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row" >
                                            <div class="col-md-6 d-flex align-items-center justify-content-center flex-column" id="verifySpot2" >
                                            </div>
                                            <div class="col-md-6" id="courses">
                                                <form>
                                                <div class="form-group row">
                                                    <label class="col-sm-3 col-form-label">Course</label>
                                                    <div class="col-sm-9">
                                                    <select class="form-control" id="selectedCourse">
                                                      ${res.courses}
                                                    </select>
                                                    </div>
                                                </div>
                                                <!-- Inputs -->
                                                <div class="form-group" classes>
                                                    <div class="input-group">
                                                    <input type="text" class="form-control" value="${res.group_id}" disabled  placeholder="${res.group_id}" aria-label="Recipient username">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-sm btn-facebook" type="button">
                                                        <i class="typcn">@</i>
                                                        </button>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="inputTwo">Course Group Rep ID</label>
                                                    <input type="text" class="form-control" id="inputTwo" value="${res.student_id}" disabled placeholder="${res.student_id}">
                                                </div>
                                                <label class="col-sm-3 col-form-label">Session Status</label>
                                                    <div class="col-sm-4">
                                                        <div class="form-check session-status">

                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer d-flex justify-content-between">
                                            <button type="button" class="btn btn-light" id="data-dismiss" data-dismiss="modal">Cancel</button>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-secondary startClass">
                                                <i class="mdi mdi-contact-mail mr-2 "></i>Build Session</button>
                                            <button type="button" class="btn btn-sm btn-secondary" id="setC" >Active Session</button>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                <script src="../backend/code/qrCode.js"></script>`

                                );

                }
            } catch (err) {
                console.error("Fetch error:", err);
            }

        }, 1000);
    });

});
