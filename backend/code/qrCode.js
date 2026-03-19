$(".startClass").click(()=>{

    async function generateQRcode(callBackData){
        // Get location first
        // navigator.geolocation.getCurrentPosition(async position => {
        //     const latitude = position.coords.latitude;
        //     const longitude = position.coords.longitude;

            let qrCodeurl = "../backend/code/qrServer.php";
            await fetch(qrCodeurl ,  {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                // body: JSON.stringify({ 
                //     latitude: latitude,
                //     longitude: longitude
                // })
            }).then(
                res => {
                    res = res.json().then( 
                        res => {
                            const data = res;
                            console.log(data);    
                            callBackData(data);
                            if(data.status == "success"){
                                $("#loadSession").fadeOut();
                            }
                        }                
                    ).catch(e => {
                        let perm = Notification.requestPermission(perm => {
                            return perm 
                        });
                        if(perm === "granted"){
                            new Notification("Infotess Uew" , {
                                body: "Please Your Location service down or check your internet connection and try again"
                            })
                        }
                        alert("Please your location service is down genreate new one and try again" , perm ," okay");
                    });
                }
            ).catch(error => {
                throw new Error("Failed fetching Data from Server");
            });
        // });
    }
        
    async function codePreview(code){ 
        $("#verifySpot2").append(`<h1 class="font-weight-bold
        mb-xl-0 serial">Loading.....</h1>`);

        await setTimeout(()=>{
            $(".serial").fadeOut();
            $("#verifycode").fadeOut();
            $("#verifySpot2").append(`<h1 class="font-weight-bold
            mb-xl-0">${code.code_b}</h1>`);

            //uri to the page for code verification  that from main folder app/assess
            const uri = `https://192.168.43.118/infotes/main/app/assess.html?att=${code.code_b}`

            if(QRCode_count < 1){
                const Verifyattendace = new QRCode(
                    document.getElementById('verifySpot'),{
                       text: uri,
                       height: 200,
                       width:  200,
                       correctLevel:QRCode.CorrectLevel.H
                   }
               );
            }

            const Verifyattendace2 = new QRCode(
                 document.getElementById('verifySpot2'),{
                    text: uri ,
                    height: 400,
                    width:  400,
                    correctLevel:QRCode.CorrectLevel.H
                }
            );
            $("#setC").fadeOut();
        }, 2000);
    }
    
    generateQRcode(codePreview);
});


$('.startClass').fadeOut();
$("#data-dismiss").click(()=>{
    $(".modal-backdrop").fadeOut();
    $("#loadin").fadeOut();
    $("body").removeClass('modal-open');
});
$("#loadSession").click(()=>{
    $(".modal-backdrop").show();
    $("#loadin").show();
    $("body").addClass('modal-open');
});
$("#showession").click(()=>{
    $(".modal-backdrop").fadeIn();
    $("#loadin").fadeIn();
    $("body").addClass('modal-open');
});
$("#CloseM").click(()=>{
    $(".modal-backdrop").fadeOut();
    $("#loadin").fadeOut();
    $("body").removeClass('modal-open');
});
$("#data-dismiss").click(()=>{
    $("#data-dismiss").fadeOut();
});


//set the user data 
$("#setC").click(()=>{
    const activeCourse = $("#selectedCourse").val();

    // Get location
    // navigator.geolocation.getCurrentPosition(position => {
    //     const latitude = position.coords.latitude;
    //     const longitude = position.coords.longitude;

        // Data to send to PHP server
        const data = {
            name: activeCourse,
            // latitude: latitude,
            // longitude: longitude
        };

        fetch('../backend/makeSet.php', { // PHP endpoint
            method: 'POST',
            // headers: {
            //     'Content-Type': 'application/json' // Tell server we're sending JSON
            // },
            body: JSON.stringify(data) // Convert JS object to JSON string
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json(); // Parse JSON response from server
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
        });
    });
// });
