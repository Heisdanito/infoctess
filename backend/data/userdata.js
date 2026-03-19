let data;

//this will conatin alot of callback
const pageName1 = document.getElementById("core-name");
//const navTabName = document.getElementsByClassName("nav-name"); 

const getData = async (callback) => {
    await fetch("../backend/data/getData.php").then(
         
        res => {
            data = res.json().then( userData => {

                if(userData.status === "Notfetch"){
                    window.location.href = "./404.html"
                }

                if(userData.group_id !== "" && userData.code !== "" && userData.code_b !== "" && userData.code_b !== "none"){
                    const Verifyattendace = new QRCode(
                        document.getElementById('verifySpot'),{
                           text: `https://192.168.43.118/infotes/main/app/assess.html?att=${userData.code_b}`,
                           height: 200,
                           width:  200,
                           correctLevel:QRCode.CorrectLevel.H
                       }
                   ) 
                    console.log(userData.code , userData.code_b)
                    QRCode_count ++ 
                    console.log(QRCode_count);
                }

                console.log(userData)
                callback(userData);
            }
            )


        }
            
    )
}

function WorkData(userObj){
    $(document).ready(function(){
        $("#core-name").prepend(`${userObj.username}`);
        $(".nav-name").prepend(`${userObj.username} (${userObj.stu_id})`); //profile-name-dash
        $(".profile-name-dash").prepend(`${userObj.username}`)//Your last login: 21h ago from newzealand.
        $(".lastLog").prepend(`Your last login: ${userObj.lastlogin} from newzealand`)
    })


}