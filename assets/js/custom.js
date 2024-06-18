$(document).ready(function() {
    $('select').niceSelect();
});

(function($) {
    "use strict";
    var HealthCare = {
        initialised: false,
        version: 1.0,
        mobile: false,
        init: function() {
            if (!this.initialised) {
                this.initialised = true;
            } else {
                return;
            }
            this.am_testimonial_slider();
            this.am_partner_slider();
            this.am_team_slider();
            this.am_feedback_slider();
            this.toggle_menu();
            this.search_btn();
        },


        // testimonial slider
        am_testimonial_slider: function() {
            var swiper = new Swiper(".hc-news", {
                slidesPerView: 2,
                spaceBetween: 30,
                loop: true,
                speed: 1000,
                pagination: {
                    el: ".hc-news .swiper-pagination",
                    clickable: true,
                },

                breakpoints: {
                    1199: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                    992: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    575: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    0: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                },
            });
        },
        // testimonial slider
        // partner slider
        am_partner_slider: function() {
            var swiper = new Swiper(".hc-depcard", {
                slidesPerView: 4,
                spaceBetween: 20,
                loop: true,
                speed: 1000,
                pagination: {
                    el: "hc-department-wrapper .swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: " .hc-department-wrapper .swiper-button-next",
                    prevEl: ".hc-department-wrapper .swiper-button-prev",
                },

                breakpoints: {
                    1199: {
                        slidesPerView: 4,
                        spaceBetween: 20,
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    575: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                    },
                    425: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    280: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                },
            });
        },
        // partner slider
        // team slider
        am_team_slider: function() {
            var swiper = new Swiper(".hc-team-row", {
                slidesPerView: 4,
                spaceBetween: 20,
                loop: true,
                speed: 1000,

                pagination: {
                    el: ".hc-team-row .swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: " .hc-depicon .swiper-button-next",
                    prevEl: ".hc-depicon .swiper-button-prev",
                },

                breakpoints: {
                    1199: {
                        slidesPerView: 4,
                        spaceBetween: 20,
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    575: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                    },
                    425: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    280: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                },
            });
        },
        // team slider
        // feedback slider
        am_feedback_slider: function() {
            var swiper = new Swiper(".hc-testimonial", {
                slidesPerView: 2,
                spaceBetween: 50,
                loop: true,
                speed: 1000,

                pagination: {
                    el: ".hc-testimonial-wrapper .swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: " .hc-testimonial-wrapper .swiper-button-next",
                    prevEl: ".hc-testimonial-wrapper .swiper-button-prev",
                },

                breakpoints: {
                    1199: {
                        slidesPerView: 2,
                        spaceBetween: 50,
                    },
                    992: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    575: {
                        slidesPerView: 2,
                        spaceBetween: 15,
                    },
                    425: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                    280: {
                        slidesPerView: 1,
                        spaceBetween: 15,
                    },
                },
            });
        },
        // feedbackslider
        // toggle menu
        toggle_menu: function() {
            $(".hc-toggle-btn").on("click", function() {
                $("body").toggleClass("menu-open");
            });
        },
        // toggle menu
        // search btn
        search_btn: function() {
            $(".searchBtn").on("click", function() {
                $(".searchBox").addClass("show");
            });
            $(".closeBtn").on("click", function() {
                $(".searchBox").removeClass("show");
            });
            $(".searchBox").on("click", function() {
                $(".searchBox").removeClass("show");
            });
            $(".search-bar-inner").on("click", function() {
                event.stopPropagation();
            });
        },
        // search btn end

    };
    HealthCare.init();
})(jQuery);




//Create references to the dropdown's
const yearSelect = document.getElementById("year");
const monthSelect = document.getElementById("month");
const daySelect = document.getElementById("day");

const months = ['Jan', 'Feb', 'Mar', 'Apr',
    'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
    'Nov', 'Dec'
];

//Months are always the same
(function populateMonths() {
    for (let i = 0; i < months.length; i++) {
        const option = document.createElement('option');
        option.textContent = months[i];
        monthSelect.appendChild(option);
    }
    monthSelect.value = "Jan";
})();

let previousDay;

function populateDays(month) {
    //Delete all of the children of the day dropdown
    //if they do exist
    while (daySelect.firstChild) {
        daySelect.removeChild(daySelect.firstChild);
    }
    //Holds the number of days in the month
    let dayNum;
    //Get the current year
    let year = yearSelect.value;

    if (month === 'January' || month === 'March' ||
        month === 'May' || month === 'July' || month === 'August' ||
        month === 'October' || month === 'December') {
        dayNum = 31;
    } else if (month === 'April' || month === 'June' ||
        month === 'September' || month === 'November') {
        dayNum = 30;
    } else {
        //Check for a leap year
        if (new Date(year, 1, 29).getMonth() === 1) {
            dayNum = 29;
        } else {
            dayNum = 28;
        }
    }
    //Insert the correct days into the day <select>
    for (let i = 1; i <= dayNum; i++) {
        const option = document.createElement("option");
        option.textContent = i;
        daySelect.appendChild(option);
    }
    if (previousDay) {
        daySelect.value = previousDay;
        if (daySelect.value === "") {
            daySelect.value = previousDay - 1;
        }
        if (daySelect.value === "") {
            daySelect.value = previousDay - 2;
        }
        if (daySelect.value === "") {
            daySelect.value = previousDay - 3;
        }
    }
}

function populateYears() {
    //Get the current year as a number
    let year = new Date().getFullYear();
    //Make the previous 100 years be an option
    for (let i = 0; i < 101; i++) {
        const option = document.createElement("option");
        option.textContent = year - i;
        yearSelect.appendChild(option);
    }
}

populateDays(monthSelect.value);
populateYears();

yearSelect.onchange = function() {
    populateDays(monthSelect.value);
}
monthSelect.onchange = function() {
    populateDays(monthSelect.value);
}
daySelect.onchange = function() {
    previousDay = daySelect.value;
}