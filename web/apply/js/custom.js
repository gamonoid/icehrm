"use strict";
$(document).ready(function() {
    $("select").niceSelect();
    /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        AOS Animation Activation
    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
    AOS.init();
    window.addEventListener("load", AOS.refresh);

    if (jQuery(".testimonial-slider-one").length > 0) {
        $(".testimonial-slider-one").slick({
            dots: true,
            infinite: true,
            arrows: false,
            speed: 500,
            slidesPerRow: 1
        });
    }

    /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>      
        Bootstrap Mobile Megamenu Support
    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/

    $(".dropdown-menu a.dropdown-toggle").on("click", function(e) {
        if (!$(this).next().hasClass("show")) {
            $(this)
                .parents(".dropdown-menu")
                .first()
                .find(".show")
                .removeClass("show");
        }
        var $subMenu = $(this).next(".dropdown-menu");
        $subMenu.toggleClass("show");

        $(this)
            .parents("li.nav-item.dropdown.show")
            .on("hidden.bs.dropdown", function(e) {
                $(".dropdown-submenu .show").removeClass("show");
            });

        return false;
    });

    /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>      
           Sticky Header
    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
    window.onscroll = function() {
        scrollFunction();
    };

    function scrollFunction() {
        if (
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20
        ) {
            $(".site-header--sticky").addClass("scrolling");
        } else {
            $(".site-header--sticky").removeClass("scrolling");
        }
        if (
            document.body.scrollTop > 300 ||
            document.documentElement.scrollTop > 300
        ) {
            $(".site-header--sticky.scrolling").addClass("reveal-header");
        } else {
            $(".site-header--sticky.scrolling").removeClass("reveal-header");
        }
    }

    /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>      
           Input Count Up Button
    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
    $(".count-btn").on("click", function() {
        var $button = $(this);
        var oldValue = $button
            .parent(".count-input-btns")
            .parent()
            .find("input")
            .val();
        if ($button.hasClass("inc-ammount")) {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            // Don't allow decrementing below zero
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 0;
            }
        }
        $button.parent(".count-input-btns").parent().find("input").val(newVal);
    });

    /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>      
           Prcing Dynamic Script
    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
    $("[data-pricing-trigger]").on("click", function(e) {
        $(e.target).addClass("active").siblings().removeClass("active");
        var target = $(e.target).attr("data-target");
        console.log($(target).attr("data-value-active") == "monthly");
        if ($(target).attr("data-value-active") == "monthly") {
            $(target).attr("data-value-active", "yearly");
        } else {
            $(target).attr("data-value-active", "monthly");
        }
    });

    /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>      
           Smooth Scroll
    <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/

    $(".goto").on("click", function(event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            $("html, body").animate({
                    scrollTop: $(hash).offset().top,
                },
                2000,
                function() {
                    window.location.hash = hash;
                }
            );
        } // End if
    });
})


/*----------  Range Slider  ----------*/
$(function() {
    $(".pm-range-slider").slider({
        range: true,
        min: 50,
        max: 180,
        values: [100, 130],
        slide: function(event, ui) {
            $("#amount").val("$" + ui.values[0] + " - " + ui.values[1] + "K");
        }
    });
    $("#amount").val("$" + $(".pm-range-slider").slider("values", 0) +
        " - " + $(".pm-range-slider").slider("values", 1) + "K");
});


$('.product-view-mode a').on('click', function(e) {
    e.preventDefault();

    var shopProductWrap = $('.shop-product-wrap');
    var viewMode = $(this).data('target');

    $('.product-view-mode a').removeClass('active');
    $(this).addClass('active');
    shopProductWrap.removeClass('grid list').addClass(viewMode);
    if (shopProductWrap.hasClass('grid')) {
        $('.pm-product').removeClass('product-type-list')
    } else {
        $('.pm-product').addClass('product-type-list')
    }
})


/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>      
      Preloader Activation
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/

$(window).load(function() {
    setTimeout(function() {
        $("#loading").fadeOut(500);
    }, 1000);
    setTimeout(function() {
        $("#loading").remove();
    }, 2000);
});



function showPassword() {
    let getElm = document.querySelectorAll('.show-password');
    for (let index = 0; index < getElm.length; index++) {
        getElm[index].addEventListener('click', function(e) {
            if (e.target.classList.contains('show')) {
                e.target.classList.remove('show');
            } else {
                e.target.classList.add("show");
            }
            var target = e.target.getAttribute("data-show-pass");
            let x = document.getElementById(target);
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        })

    }

}
showPassword();


/*----------  Range Slider  ----------*/

function toggleItem(params) {
    let getItem = document.querySelectorAll(".toggle-item");
    for (let i = 0; i < getItem.length; i++) {
        getItem[i].addEventListener('click', function(e) {
            if (e.target.classList.contains("clicked")) {
                e.target.classList.remove("clicked");
            } else {
                e.target.classList.add("clicked");
            }
        })
    }
}
toggleItem();

/*---------- Counter Up ------------ */
$('.counter').counterUp({
    delay: 20,
    time: 2000
});