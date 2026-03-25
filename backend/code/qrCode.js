// ── Get location — keeps retrying silently until it succeeds ─────────────
function getLocation(callback, retries = 5) {
    navigator.geolocation.getCurrentPosition(
        position => {
            callback(position.coords.latitude, position.coords.longitude);
        },
        error => {
            if (retries > 0) {
                // Silent retry
                setTimeout(() => getLocation(callback, retries - 1), 1000);
            } else {
                // All retries failed — bypass with null
                callback(null, null);
            }
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}


// ── Set course ────────────────────────────────────────────────────────────
$("#setC").click(() => {
    const activeCourse = $("#selectedCourse").val();

    getLocation((latitude, longitude) => {
        const data = {
            name:      activeCourse,
            latitude:  latitude,
            longitude: longitude
        };

        fetch('../backend/makeSet.php', {
            method: 'POST',
            body:   JSON.stringify(data)
        })
        .then(response => response.json())
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
        });
    });
});


// ── Generate QR ───────────────────────────────────────────────────────────
$(".startClass").click(() => {

    async function generateQRcode(callBackData) {
        getLocation(async (latitude, longitude) => {
            await fetch("../backend/code/qrServer.php", {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ latitude, longitude })
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
            });
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
                    text: uri, height: 200, width: 200, correctLevel: QRCode.CorrectLevel.H
                });
            }

            new QRCode(document.getElementById('verifySpot2'), {
                text: uri, height: 400, width: 400, correctLevel: QRCode.CorrectLevel.H
            });

            $("#setC").fadeOut();
        }, 2000);
    }

    generateQRcode(codePreview);
});
