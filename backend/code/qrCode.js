let QRCode_count = 0;

// ── Set course before starting session ────────────────────────────────────
$("#setC").click(() => {
    const activeCourse = $("#selectedCourse").val();

    if (!activeCourse) {
        alert("Please select a course first");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        position => {
            const latitude  = position.coords.latitude;
            const longitude = position.coords.longitude;

            const data = {
                name:      activeCourse,
                latitude:  latitude,
                longitude: longitude
            };

            fetch('../backend/makeSet.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(result => {
                console.log('Server response:', result);
                $(".set").remove();
                $(".session-status").append(`<label class="form-check-label set">${result.data}</label>`);
                $('.startClass').fadeIn();
                $("#showession").fadeIn();
                $('#setC').fadeOut();
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Failed to set course. Check your connection and try again.");
            });
        },
        error => {
            alert("Location access denied. Please enable location and try again.");
        }
    );
});

// ── Start class / Generate QR ─────────────────────────────────────────────
$(".startClass").click(() => {

    async function generateQRcode(callBackData) {
        navigator.geolocation.getCurrentPosition(async position => {
            const latitude  = position.coords.latitude;
            const longitude = position.coords.longitude;

            let qrCodeurl = "../backend/code/qrServer.php";

            await fetch(qrCodeurl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    latitude:  latitude,
                    longitude: longitude
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log(data);
                callBackData(data);
                if (data.status == "success") {
                    $("#loadSession").fadeOut();
                }
            })
            .catch(e => {
                console.error("QR generation error:", e);
                alert("Location service is down. Generate a new one and try again.");
            });

        }, error => {
            alert("Location access denied. Please enable location and try again.");
        });
    }

    async function codePreview(code) {
        $("#verifySpot2").append(`<h1 class="font-weight-bold mb-xl-0 serial">Loading.....</h1>`);

        await setTimeout(() => {
            $(".serial").fadeOut();
            $("#verifycode").fadeOut();
            $("#verifySpot2").append(`<h1 class="font-weight-bold mb-xl-0">${code.code_b}</h1>`);

            const uri = `https://infoctess-production.up.railway.app/app/assess.html?att=${code.code_b}`;

            if (QRCode_count < 1) {
                new QRCode(document.getElementById('verifySpot'), {
                    text:         uri,
                    height:       200,
                    width:        200,
                    correctLevel: QRCode.CorrectLevel.H
                });
            }

            new QRCode(document.getElementById('verifySpot2'), {
                text:         uri,
                height:       400,
                width:        400,
                correctLevel: QRCode.CorrectLevel.H
            });

            $("#setC").fadeOut();
        }, 2000);
    }

    generateQRcode(codePreview);
});

// ── Modal controls ────────────────────────────────────────────────────────
$('.startClass').fadeOut();

$("#data-dismiss").click(() => {
    $(".modal-backdrop").fadeOut();
    $("#loadin").fadeOut();
    $("body").removeClass('modal-open');
});

$("#loadSession").click(() => {
    $(".modal-backdrop").show();
    $("#loadin").show();
    $("body").addClass('modal-open');
});

$("#showession").click(() => {
    $(".modal-backdrop").fadeIn();
    $("#loadin").fadeIn();
    $("body").addClass('modal-open');
});

$("#CloseM").click(() => {
    $(".modal-backdrop").fadeOut();
    $("#loadin").fadeOut();
    $("body").removeClass('modal-open');
});
