<?php

namespace ElementPack\Includes\Builder;

if ( ! defined( 'WPINC' ) ) {
	die;
}

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Core\DocumentTypes\PageBase;
use ElementPack\Base\Singleton;
use ElementPack\Includes\Builder\Meta;
use ElementPack\Includes\Builder\Builder_Template_Helper;
use ElementPack\Includes\Builder\Builder_Post_Singleton;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;


class Builder_Integration {

	use Singleton;

	private $current_template = null;
	public $current_template_id = null;

	function __construct() {
		add_filter( 'template_include', [ $this, 'set_builder_template' ], 9999 );
		add_action( 'elementor/editor/init', [ $this, 'set_sample_post' ], 999 );

		add_action( 'print_default_editor_scripts', array( $this, 'my_custom_fonts' ) );

		add_action( 'elementor/documents/register_controls', [ $this, 'register_document_controls' ] );
	}

	public function my_custom_fonts() {
		if ( is_admin() && Plugin::instance()->editor->is_edit_mode() ) {
			if ( isset( $_REQUEST['bdt-template'] ) ) {
				wp_register_style( 'bdt-template-builder-hide-preview-btn-inline', false ); // phpcs:ignore
				wp_enqueue_style( 'bdt-template-builder-hide-preview-btn-inline' );
				wp_add_inline_style(
					'bdt-template-builder-hide-preview-btn-inline',
					'#elementor-panel-footer-saver-preview {display:none!important}'
				);
			}
		}
	}
	function set_sample_post() {
		if ( Builder_Template_Helper::isTemplateEditMode() ) {
			$object = Builder_Post_Singleton::instance();
			$object::set_sample_post();
		}
	}

	function register_document_controls( $document ) {
		if ( ! $document instanceof PageBase || ! $document::get_property( 'has_elements' ) ) {
			return;
		}

		if ( Plugin::instance()->preview->is_preview_mode() )
			return;

		if ( ! Builder_Template_Helper::isTemplateEditMode() ) {
			return;
		}

		global $post;

		if ( ! isset( $post->ID ) ) {
			return;
		}
		$meta = get_post_meta( $post->ID );

		$templateMeta = optional( $meta )[ Meta::TEMPLATE_TYPE ];
		if ( ! isset( $templateMeta[0] ) ) {
			return;
		}
		$postMeta = $templateMeta[0];
		$postMeta = explode( '|', $postMeta );
		$postType = $postMeta[0];

		if ( 'single' != $postMeta[1] ) {
			return;
		}

		$document->start_controls_section(
			'bdt_page_setting_preview',
			[ 
				'label' => esc_html__( 'Builder Settings', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			]
		);

		$document->add_control(
			'bdt_builder_sample_post_id',
			[ 
				'label'       => esc_html__( 'Builder Post', 'bdthemes-element-pack' ),
				'type'        => Dynamic_Select::TYPE,
				'multiple'    => false,
				'label_block' => true,
				'query_args'  => [ 
					'post_type' => $postType
				],
			]
		);

		$document->add_control(
			'bdt_builder_sample_apply_preview',
			[ 
				'type'        => Controls_Manager::BUTTON,
				'label'       => esc_html__( 'Apply & Preview', 'bdthemes-element-pack' ),
				'label_block' => true,
				'show_label'  => false,
				'text'        => esc_html__( 'Apply & Preview', 'bdthemes-element-pack' ),
				'separator'   => 'none',
				'event'       => 'ElementPackBuilderSetting:applySinglePagePostOnPreview',
			]
		);

		$document->end_controls_section();
	}

	/**
	 * Rewrite default template
	 *
	 */
	function set_builder_template( $template ) {
		if ( Builder_Template_Helper::isTemplateEditMode() ) {
			return $this->setBackendTemplate( $template );
		} else {
			return $this->setFrontendTemplate( $template );
		}
	}


	protected function setBackendTemplate( $template ) {
		return $template;
	}


	protected function setFrontendTemplate( $template ) {
		// if (get_post_type() == 'product') {
		//     global $product;
		//     $product = wc_get_product();
		// }



		if ( defined( 'ELEMENTOR_PATH' ) ) {
			$elementorTem = ELEMENTOR_PATH . "modules/page-templates/templates/";
			$elementorTem = explode( $elementorTem, $template );
			if ( count( $elementorTem ) == 2 ) {
				return $template;
			}
		}

		// single posts
		if ( is_single() && 'post' == get_post_type() ) {
			//single__3168
			if ( $custom_template = $this->get_template_id( 'single', 'post' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'posts/single', $template );
			}
		}

		// archive page
		if ( ( is_archive() || is_home() ) && get_post_type( get_the_ID() ) == 'post' ) {
			if ( is_category() ) {
				if ( $custom_template = $this->get_template_id( 'category', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/category', $template );
				}
			} elseif ( is_tag() ) {
				if ( $custom_template = $this->get_template_id( 'tag', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/tag', $template );
				}
			} elseif ( is_author() ) {
				if ( $custom_template = $this->get_template_id( 'author', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/author', $template );
				}
			} elseif ( is_date() ) {
				if ( $custom_template = $this->get_template_id( 'date', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/date', $template );
				}
			} else {
				if ( $custom_template = $this->get_template_id( 'archive', 'post' ) ) {
					$this->current_template_id = $custom_template;
					return $this->getTemplatePath( 'posts/archive', $template );
				}
			}
		}

		// Pages
		if ( is_page() && ! is_page_template() && 'page' == get_post_type() ) {
			if ( $custom_template = $this->get_template_id( 'single', 'page' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'pages/single', $template );
			}
		}

		//  404 page
		if ( is_404() ) {
			if ( $custom_template = $this->get_template_id( '404', 'page' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'pages/404', $template );
			}
		}

		// search page
		if ( is_search() ) {
			if ( $custom_template = $this->get_template_id( 'search', 'page' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'pages/search', $template );
			}
		}
		// front page
		// if (is_front_page()) {
		//     if ($custom_template = $this->get_template_id('home', 'page')) {
		//         $this->current_template_id = $custom_template;
		//         return $this->getTemplatePath('pages/home', $template);
		//     }
		// }


		// themes header
		if ( is_page() && ! is_page_template() && 'themes' == get_post_type() ) {
			if ( $custom_template = $this->get_template_id( 'header', 'themes' ) ) {
				$this->current_template_id = $custom_template;
				return $this->getTemplatePath( 'themes/header', $template );
			}
		}

		return $template;
	}


	public function getThemeTemplatePath( $slug ) {

		$fullPath = get_template_directory() . "/bdthemes-element-pack/$slug";
		if ( file_exists( $fullPath ) ) {
			return $fullPath;
		}
	}

	public function getPluginTemplatePath( $slug ) {

		$fullPath = BDTEP_PATH . "includes/builder/templates/$slug";
		if ( file_exists( $fullPath ) ) {
			return $fullPath;
		}
	}


	/**
	 * Get Template Path ID
	 *
	 * @param $slug
	 * @param $postType
	 *
	 * @return mixed|void|null
	 */
	public function get_template_id( $slug, $postType = false ) {

		if ( 'single' == $slug && 'post' == $postType ) {
			$pattern = '_bdthemes_builder_post|single__';
			$results = Builder_Template_Helper::searchTemplateOptions( $pattern );

			$option_values = array_map( function ($result) {
				return $result->option_value;
			}, $results );
			$slug          = 'single__' . implode( ', ', $option_values );
		}

		$templateId                = Builder_Template_Helper::getTemplate( $slug, $postType );
		$this->current_template_id = apply_filters( 'bdthemes-templates-builder/custom-template', $templateId );

		return $this->current_template_id;
	}


	/**
	 * Get Template Path
	 *
	 * @param $slug
	 * @param $default
	 *
	 * @return mixed|string|void
	 */
	protected function getTemplatePath( $slug, $default = '' ) {
		$phpSlug = "{$slug}.php";

		if ( $template = $this->getThemeTemplatePath( $phpSlug ) ) {
			return $template;
		}

		if ( $template = $this->getPluginTemplatePath( $phpSlug ) ) {
			return $template;
		}

		return $default;
	}
}


Builder_Integration::instance();
