jQuery(document).ready(function($){
	// Add color picker
    $('.cnb-color-field').wpColorPicker();
    $('.cnb-iconcolor-field').wpColorPicker();

	// Reveal additional button placements when clicking "more"
	$("#cnb-more-placements").click(function(e){
		e.preventDefault();
		$(".cnb-extra-placement").css("display","block");
		$("#cnb-more-placements").remove();
	});

	// Option to Hide Icon is only visible when the full width button is selected
	var radioValue = $("input[name='cnb[appearance]']:checked").val();
	var textValue 	= $("input[name='cnb[text]']").val();
	if(radioValue != 'full' && radioValue != 'tfull') {
		$('#hideIconTR').hide();
	} else if(textValue.length < 1) {
		$('#hideIconTR').hide();
	}
    $('input[name="cnb[appearance]"]').on("change",function(){
    	var radioValue 	= $("input[name='cnb[appearance]']:checked").val();
		var textValue 	= $("input[name='cnb[text]']").val();
        if(radioValue != 'full' && radioValue != 'tfull') {
            $('#hideIconTR').hide();
        } else if(textValue.length > 0 ) {
            $('#hideIconTR').show();
        }

    });
});
// Zoom slider - show percentage
var cnb_slider = document.getElementById("cnb_slider");
var cnb_slider_value = document.getElementById("cnb_slider_value");
cnb_slider_value.innerHTML = Math.round(cnb_slider.value * 100) + "%";
cnb_slider.oninput = function() {
  cnb_slider_value.innerHTML = Math.round(this.value * 100) + "%";
}

// Z-index slider - show steps
var cnb_order_slider = document.getElementById("cnb_order_slider");
var cnb_order_value = document.getElementById("cnb_order_value");
cnb_order_value.innerHTML = cnb_order_slider.value;
cnb_order_slider.oninput = function() {
  cnb_order_value.innerHTML = this.value;
}
