jQuery(document).ready(function ($) {

	// Initialiser tous les tooltips Bootstrap
	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

	console.log('✅ Tooltips Bootstrap initialisés:', tooltipList.length);

});

