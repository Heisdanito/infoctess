let progressCircle = null;
let studentId = null;
let groupId = null;
let allCourses = [];

// Initialize progress circle safely
document.addEventListener("DOMContentLoaded", function() {
    const circleContainer = document.getElementById("attendanceProgress");
    if (circleContainer) {
        try {
            progressCircle = new ProgressBar.Circle(circleContainer, {
                color: "#4f6ef7",
                trailColor: "#e9ecef",
                trailWidth: 15,
                duration: 1500,
                easing: "bounce",
                strokeWidth: 10,
                text: { autoStyleContainer: false },
                step: function(state, circle) {
                    circle.setText((circle.value() * 100).toFixed(0) + "%");
                }
            });
        } catch (e) {
            console.warn("ProgressBar init failed:", e);
        }
    }

    // Extra safe init for #circleProgress6 if present
    const circle6 = document.getElementById("circleProgress6");
    if (circle6) {
        try {
            const bar = new ProgressBar.Circle(circle6, {
                color: "#3B1DE3",
                strokeWidth: 6,
                trailWidth: 2,
                trailColor: "#e0e0e0",
                duration: 1400,
                easing: "easeInOut"
            });
            bar.animate(0.7); // Example animation
        } catch (e) {
            console.warn("circleProgress6 init failed:", e);
        }
    }

    // Load student info first
    loadStudentInfo();

    // Course search functionality
    document.getElementById("courseSearch").addEventListener("keyup", function() {
        const search = this.value.toLowerCase();
        const items = document.querySelectorAll(".course-item");
        items.forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(search) ? "block" : "none";
        });
    });
});
