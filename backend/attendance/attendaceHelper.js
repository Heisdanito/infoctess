function studentData(callbackFn){
    setTimeout(async ()=>{
        const userDataurl = "../backend/data/user.php";
        await fetch(userDataurl , {method: "POST"})
            .then(response => response.json())
            .then(student => {
                if(student.status === "fetched"){
                    $('.sidebar-profile-name').append(`<p class="sidebar-name">${student.username}</p>`);
                    $('.body-name').append(`<h3 class="mb-0 font-weight-bold">${student.username}</h3><p>Your last login: ${student.lastlogin} from Winneba.</p>`);
                    
                    // Call the callback after successful fetch
                    if (typeof callbackFn === "function") {
                        callbackFn();
                    }

                } else {
                    window.location.href = "../auth/login.html";
                }
            })
            .catch(e => console.error(e));
    }, 1000);
}


function TablesData(progressFn){
    const url = "../backend/attendance/student-att.php";
    fetch(url , {method: "POST"})
        .then(stuTable => stuTable.json())
        .then(student => {
            $('#total-classes').append(`<div>${student.overall.total_classes }(${student.overall.percentage}% )</div>`);
            $('#missed-classes').append(`<div>${student.overall.missed}(out of ${student.overall.total_classes})</div>`)
            $('#percentage').append(`<div>${student.overall.percentage}%(100%)</div>`)

            //define remarrks
            if(student.overall.percentage <= 45 ){
              $('#Remarks').append(`<div><h4 class="text-danger">Bad Att</div>`)
            }else if(student.overall.percentage <= 60 ){
              $('#Remarks').append(`<div><h4 class="text-primary">Low </div>`)
            }
            else if(student.overall.percentage <= 80 ){
              $('#Remarks').append(`<div><h4 style="color: orange;"></div>`)
            }else{
              $('#Remarks').append(`<div><h4 class="text-success">Punctual</div>`)
            }

            let coursesArr = student.data 
            console.log(coursesArr[0]);
            
            for(i = 0 ; i <= coursesArr.length - 1 ; i++ ){
              // console.log(coursesArr[i].data)
              $('#table-data').append(coursesArr[i].data)
            }


            console.log(student);
            // callback for progresss function
            if (typeof progressFn === "function") {
                progressFn(student);
            }
        })
        .catch(e => console.error(e));
}


// Now pass progressBar as the callback
studentData(() => TablesData(progressBar));


    function progressBar(getStudent){
        if ($('#circleProgress6').length) {
      var bar = new ProgressBar.Circle(circleProgress6, {
        color: '#001737',
        // This has to be the same size as the maximum width to
        // prevent clipping
        strokeWidth: 10,
        trailWidth: 10,
        easing: 'easeInOut',
        duration: 1400,
        text: {
          autoStyleContainer: false
        },
        from: {
          color: '#aaa',
          width: 10
        },
        to: {
          color: '#2617c9',
          width: 10
        },
        // Set default step function for all animate calls
        step: function(state, circle) {
          circle.path.setAttribute('stroke', state.color);
          circle.path.setAttribute('stroke-width', state.width);
  
          var value = '<p class="text-center mb-0">Score</p>' + Math.round(circle.value() * 100) + "%";
          if (value === 0) {
            circle.setText('');
          } else {
            circle.setText(value);
          }
  
        }
      });
  
      bar.text.style.fontSize = '1.875rem';
      bar.text.style.fontWeight = '700';
      bar.animate(getStudent.overall.percentage/100); // Number from 0.0 to 1.0
    }
    }    
