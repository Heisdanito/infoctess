// Check and run QR expiry
fetch("../backend/code/timeExpiryHandler.php")
  .then(r => console.log(r))
  .catch(e => console.error("QR expiry error:", e));

let course;

// Auto-refresh attendance table every 3 seconds
setInterval(async () => {
  const urlTable = '../backend/attendance/apigetTable.php';
  try {
    const res = await fetch(urlTable, { method: "POST" });
    const tableData = await res.json();

    $('.new-row').remove();
    $('.loader').hide();
    $('.attendance-active').append(tableData.data);
  } catch (error) {
    console.error("Attendance table error:", error);
  }
}, 3000);

let Countdata = 0;

// Read progress bar data
async function progressBarRead(progressP) {
  const url = "../backend/attendance/progress.php";
  try {
    const res = await fetch(url);
    const progressData = await res.json();
    console.log(progressData);
    Countdata = progressData.percentage;
  } catch (error) {
    console.error("Progress bar read error:", error);
  }
}

// Initialize and animate progress bar if element exists
if ($('#circleProgress6').length) {
  const bar = new ProgressBar.Circle(circleProgress6, {
    color: '#001737',
    strokeWidth: 10,
    trailWidth: 10,
    easing: 'easeInOut',
    duration: 1400,
    text: { autoStyleContainer: false },
    from: { color: '#aaa', width: 10 },
    to: { color: '#2617c9', width: 10 },
    step: function (state, circle) {
      circle.path.setAttribute('stroke', state.color);
      circle.path.setAttribute('stroke-width', state.width);
      const value = Math.round(circle.value() * 100);
      circle.setText(value === 0 ? '' : `<p class="text-center mb-0">Score</p>${value}%`);
    }
  });

  bar.text.style.fontSize = '1.875rem';
  bar.text.style.fontWeight = '700';

  // Update progress bar every 2 seconds
  setInterval(async () => {
    try {
      const res = await fetch("../backend/attendance/progress.php");
      const progressData = await res.json();
      console.log(progressData);
      Countdata = progressData.percentage;
      bar.animate(progressData.percentage / 100);
    } catch (error) {
      console.error("Progress bar update error:", error);
    }
  }, 2000);
}