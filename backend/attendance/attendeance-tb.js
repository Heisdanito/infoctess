
async function fetchTb(){
    await fetch('../backend/attendance/apigetTable.php').then(
        res => res.json().then(
            tableData => {
                if(tableData.status === "success"){
                    $('')
                }
                if(tableData.status === 'failed'){
                    $('.')
                }

            }
        ).catch( jerror => console.error(jerror))
    ).catch(
        e => 
        console.error(e)
    )
}

fetchTb();