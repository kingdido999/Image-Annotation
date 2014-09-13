/**
 *	This script handles front-end mouse events and loads annotations using AJAX.
 *	It's based on Annotorious API: http://annotorious.github.io/api.html
 */

var $ = jQuery.noConflict();

$(document).ready(function() {
	var imageSelector = annotorious_translation_array.imageSelector;

	// Load available annotations
	$(imageSelector).one("load", function() {
		// Only make it annotable after each image is loaded
		anno.makeAnnotatable($(this)[0]);

		var url = $(this)[0]['src'];
		var data = {
			action: 'load',
			url: url
		}

		$.ajax({
			  type: "GET",
			  url: ajaxurl,
			  data: data,
			  success: function(annotations) {
			  	annotations = JSON.parse(annotations);

			  	// Add existing annotations to the image
			  	$.each(annotations, function(index, object) {
			  		var annotation = {
			  			src: url,
			  			text: object['text'],
			  			shapes: [{
			  				type: 'rect',
			  				geometry: {
			  					x: object['coordinateX'],
			  					y: object['coordinateY'],
			  					width: object['width'],
			  					height: object['height']
			  				}
			  			}]
			  		}

			  		// Widgets and annotations are hidden by default
					anno.hideSelectionWidget(url);
					anno.hideAnnotations(url);
					anno.addAnnotation(annotation);
			  	});
			  }
		});
	});

	// Show annotations when mouse enters the image
	$(imageSelector).mouseenter(function() {
		var url = $(this)[0]['src'];

		// Show widget and annotations
		anno.showSelectionWidget(url);
		anno.showAnnotations(url);
	});

	// Hide annotations when mouse leaves the image
	$('.annotorious-annotationlayer').mouseleave(function() {
		var url = $(this).find('img')[0]['src'];

		// Hide widget and annotations
		anno.hideSelectionWidget(url);
		anno.hideAnnotations(url);
	});

	// Create annotation
	anno.addHandler('onAnnotationCreated', function(annotation) {
		var data = {
			action: 'create',
			object: annotation
		};

		$.ajax({
			  type: "POST",
			  url: ajaxurl,
			  data: data,
			  success: function(response) {
			  	//console.log(response);
			  }
		});
	});

	// Update annotation
	anno.addHandler('onAnnotationUpdated', function(annotation) {
		var data = {
			action: 'update',
			object: annotation
		};

		$.ajax({
			  type: "POST",
			  url: ajaxurl,
			  data: data,
			  success: function(response) {
			  	//console.log(response);
			  }
		});
	});	

	// Remove annotation
	anno.addHandler('onAnnotationRemoved', function(annotation) {
		var data = {
			action: 'delete',
			object: annotation
		};

		$.ajax({
			  type: "POST",
			  url: ajaxurl,
			  data: data,
			  success: function(response) {
			  	//console.log(response);
			  }
		});
	});

	// Correct edit textbox css position
	// This is a css-position bug which shows edit textbox position incorrectly 
	// caused by the process of adding annotations (reason unknown)
	$('.annotorious-popup-button.annotorious-popup-button-edit').click(function() {
		var top = $(this).parent().parent().css("top");
		var editor = $(this).parent().parent().next();
		editor.css("top", top);
	});
});