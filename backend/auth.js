$(document).ready(function(){
    let key;
    function Token(){
        let Token = [];
    
        let i = Math.floor(Math.random()* 7);
        for (j = 1 ; j <= i ; j++){ 
            // console.log(j)
            //generate key from these
            let key1 = Math.floor(Math.random()* 10)
            let firstKey = "!@#$%^&*()";
            let secondKey = Math.floor(Math.random()* 100 ) + 1
            let LastKey = "abcdefghijklmnopqrst";
    
            Token.unshift(firstKey.charAt(key1));
            Token.unshift(secondKey);
            Token.unshift(LastKey.charAt(key1));
    
            // console.log(Token)
            
        }
        key;
        let keyLenght = Token.length;
        i = 0;
        while(i <= keyLenght - 1 ){ 
            key += String(Token[i])
            i++
        }
        key = String(key).slice(7,key.length)
    
        return key;
    }
    
    $("#sendAuth").click( async ()=>{
        
        const psw = $("#exampleInputPassword1").val();
//        const email = $("#exampleInputEmail1").val();

        Token();

        $.ajax({

            url: '../backend/auth.php',
            method: "POST",
            data: { psw : psw, key: key },
            success: function(response){
                // Log raw response for debugging
                console.log("Raw response:", response);
                
                try {
                    let data = JSON.parse(response)
                    console.log("Parsed data:", data)
                    
                    if(data.status === "JWTsuccess"){
                        window.location.href = `${data.nextPage}` 
                    }else{
                        
                    //show error message to the user if response appear to to unsuccessfull
                        $("#preview-list-cc").prepend(`<p class=\"mb-0 font-weight-normal float-left dropdown-header status\">${data.status}</p>`);
                        $(".message").prepend(`<h6 class=\"preview-subject font-weight-normal sd\">${data.message}</h6>`)
                        $("#preview-list-cc").fadeIn(2000);
                    setTimeout(()=>{
                        $(".status").remove()
                        $(".sd").remove()
                        $("#preview-list-cc").fadeOut(2000);
                    },4000)
                    }
                } catch(e) {
                    console.error("JSON Parse Error:", e);
                    console.error("Response was:", response);
                    $("#preview-list-cc").prepend(`<p class=\"mb-0 font-weight-normal float-left dropdown-header status\">Server Error</p>`);
                    $(".message").prepend(`<h6 class=\"preview-subject font-weight-normal sd\">Invalid response from server. Check console for details.</h6>`)
                    $("#preview-list-cc").fadeIn(2000);
                    setTimeout(()=>{
                        $(".status").remove()
                        $(".sd").remove()
                        $("#preview-list-cc").fadeOut(2000);
                    },4000)
                }

            },
            error: function(xhr, status, error){
                console.error("AJAX Error:", status, error);
                console.error("Response text:", xhr.responseText);
                alert("Error: " + error);
            }
        })
    });
})