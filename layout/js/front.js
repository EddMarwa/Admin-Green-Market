// The jQuery of the "Frontend Section" of the website



/* global $, confirm */
$(function () {
    'use strict';



    // Switching between Login or Signup forms. Check    eCommerce\login.php
    $('.login-page h1 span').click(function () {
        // console.log($(this));


        $(this).addClass('selected').siblings().removeClass('selected'); // Add the .selected CSS class to the clicked <span>, and remove it from the other siblings (the other <span>-s) at the same time (switch adding the .selected CSS class to the Login <span> and Signup <span>)    // This is used for coloring the .selected CSS class <span>. Check front.css file

        $('.login-page form').hide(); // Hide BOTH Login and Signup HTML Forms

        // Show the clicked HTML Form whether the Login or Signup Form
        // console.log($(this)); // Using Custom HTML data-* Attributes    // '.login' or '.signup'
        // console.log($(this).data('class')); // Using Custom HTML data-* Attributes    // '.login' or '.signup'

        // console.log('.' + $(this).data('class'));    // Using Custom HTML data-* Attributes    // '.login' or '.signup'
        // console.log($('.' + $(this).data('class'))); // Using Custom HTML data-* Attributes    // '.login' or '.signup'

        $('.' + $(this).data('class')).fadeIn(100);     // Using Custom HTML data-* Attributes    // '.login' or '.signup'
    });



    // Triggering (firing) the SelectBoxIt Plugin (for all Select Boxes in my application)
    $("select").selectBoxIt({
        autoWidth: false
    });



    // Hiding placeholder upon form focus
    $('[placeholder]').focus(function () {
        
        $(this).attr('data-text', $(this).attr('placeholder')); // storing the placeholder attribute value in a Custom HTML data-* Attribute data-text
        $(this).attr('placeholder', ''); // hiding the placeholder upon focus
        
    }).blur(function () {
        $(this).attr('placeholder', $(this).attr('data-text')); // Or    $(this).attr('placeholder', $(this).data('text'));
    });




    // Form Validation
    // Adding an Asterisk * on the required fields
    $('input').each(function () {
        if ($(this).attr('required') === 'required') { // Or the same code:   if ($(this).prop('required') === true) {
            $(this).after('<span class="asterisk">*</span>');
        }
    });



    // Confirmation message when Delete button in members.php is clicked
    $('.confirm').click(function () {
        return confirm('Are You Sure?');
    });



    // Newad.php Page (Beautiful Live Show)
    /******
    $('.live-name').keyup(function () {
        // console.log($(this).val());
        $('.live-preview h3').text($(this).val());
    });
    $('.live-desc').keyup(function () {
        // console.log($(this).val());
        $('.live-preview p').text($(this).val());
    });
    $('.live-price').keyup(function () {
        // console.log($(this).val());
        $('.live-preview .price-tag').text('$' + $(this).val());
    });
    ******/
    // TO REDUCE THE LAST CODE INTO ONE FUNCTION
    $('.live').keyup(function () {
        // console.log($(this).data('class')); // Here we are printing the classes themselves    // Outputs are .live-name, .live-desc, .live-price
        // console.log($($(this).data('class'))); // Here we are selecting elements not printing the classes themselves as in the previous code line
        $($(this).data('class')).text($(this).val());
    });
    
});
document.getElementById('price-input').addEventListener('input', function() {
    var price = this.value;
    var priceSpan = document.querySelector('.live-price');
    priceSpan.textContent = price;  // Update the live-price span with the current input value
});



// lease  code for lease.php
document.addEventListener("DOMContentLoaded", function() {
    let leaseMonths = document.getElementById('lease_months');
    let leaseDays = document.getElementById('lease_days');
    let totalCostDisplay = document.getElementById('total_cost');
    let securityDepositInput = document.getElementById('security_deposit');
    let productPriceElement = document.getElementById('hidden_price');

    // Ensure product price element exists
    if (!productPriceElement) {
        console.error("Product price element not found!");
        return;
    }

    let productPrice = parseFloat(productPriceElement.value);
    if (isNaN(productPrice)) {
        console.error("Invalid product price!");
        return;
    }

    function updateCosts() {
        let months = parseInt(leaseMonths.value) || 0;
        let days = parseInt(leaseDays.value) || 0;
        let pricePerDay = productPrice / 30;

        let totalCost = (months * productPrice) + (days * pricePerDay);
        let securityDeposit = productPrice * 0.5;

        // Ensure values don't go negative
        totalCost = Math.max(0, totalCost);
        securityDeposit = Math.max(0, securityDeposit);

        // Debugging: Check calculations
        console.log("Months:", months, "Days:", days);
        console.log("Calculated Total Cost:", totalCost);
        console.log("Security Deposit:", securityDeposit);

        // Update total cost with smooth transition
        totalCostDisplay.style.opacity = "0";
        setTimeout(() => {
            totalCostDisplay.textContent = "KES " + totalCost.toFixed(2);
            totalCostDisplay.style.opacity = "1";
        }, 200);

        // Update security deposit dynamically
        securityDepositInput.value = securityDeposit.toFixed(2);
    }

    // Bind input listeners
    leaseMonths.addEventListener('input', updateCosts);
    leaseDays.addEventListener('input', updateCosts);

    // Initialize the cost update on page load
    updateCosts();
});
