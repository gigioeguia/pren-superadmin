// Sidebar arrow icon rotation---------------------->
const sidebarArrow = document.getElementById("arrowIcon");
const toggleBtn = document.getElementById("toggleArrow");
toggleBtn.addEventListener("click", () => {
    sidebarArrow.classList.toggle("rotate-right");
});

document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".woodland-sidebar-open");
    const sidebarPlan = document.querySelector(".lg-sub-plan");
    const subPlan = document.querySelector(".sub-plan");


    toggleBtn.addEventListener("click", function () {
        if (sidebarPlan.style.display === "none") {
            sidebarPlan.style.display = "block";
            subPlan.style.display = "none";

        } else {
            sidebarPlan.style.display = "none";
            subPlan.style.display = "block";

        }
    });
});

