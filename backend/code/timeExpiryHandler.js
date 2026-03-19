$(document).ready(function(){    
    //get time 

    let url =  "../backend/code/timeExpiryHandler.php";
    fetch(url).then(r => {
         r.json().then(dataTime => {

            const dateNow = new Date();
            let hours = new Date().getHours();
            let min = new Date().getMinutes();

            //make time format varirable for calculations
            let db_dt = dataTime.code
            let db_time = db_dt.slice(db_dt.indexOf(" "), db_dt.length);
            //grab db hours
            let db_hours = db_time.slice(1,4)
            db_hours  = db_hours
            //mintues from database
            let db_min = db_time.slice(4,6)
            console.log(db_time)
            //check for datae expiry call
            // if()
            console.log(dateNow , " db hours " , Number(db_hours) , " db min " , db_min)
            console.log(dateNow , " day " , hours , " min " , min)
            console.log(dataTime)
        })
    })

});