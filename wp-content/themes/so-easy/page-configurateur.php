<?php
/**
 * Template Name: Page Configurateur SoEasy
 */

get_header();

?>

<div class="configurateur-wrapper container-fluid py-5" data-current-step="1">
	

	<!-- Colonne gauche (étapes) + colonne droite (récapitulatif) -->
	<div class="row">
		<div class="col-lg-9" id="config-step-content">
			<?php get_template_part('configurateur/step', '1-adresses'); ?>
		</div>

		<?php get_template_part('configurateur/sidebar-recap'); ?>
	</div>
</div>

<script>
	const soeasyAdresses = <?php echo json_encode(soeasy_get_adresses_configurateur()); ?>;
	localStorage.setItem('soeasyAdresses', JSON.stringify(soeasyAdresses));
</script>

<?php get_footer(); ?>