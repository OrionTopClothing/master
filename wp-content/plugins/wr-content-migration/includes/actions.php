<?php
/**
 * @version    1.0
 * @package    WR_Content_Migration
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

/**
 * Define class that create form to import post content.
 */
class WR_Content_Migration_Actions {
	/**
	 * Initialize.
	 *
	 * @return  void
	 */
	public static function initialize() {
		// Register action to display export / import form.
		add_action( 'wr_content_migration_render_form', array( __CLASS__, 'display' ) );

		// Register action to handle request to export content.
		add_action( 'template_redirect', array( __CLASS__, 'export' ), 9999 );

		// Register action to handle request to import content.
		add_action( 'template_redirect', array( __CLASS__, 'import' ), 9999 );

		// Register filter to add more supported MIME types.
		add_filter( 'upload_mimes', array( __CLASS__, 'upload_mimes' ) );
	}

	/**
	 * Display form to export / import content.
	 *
	 * @param   string  $layout  If 'full', display a full grid-based layout without upload form.
	 *                           If 'form', display a simple upload form with a select box to select content preset.
	 *
	 * @return  void
	 */
	public static function display( $layout = 'form' ) {
		if ( ! is_admin() ) {
			return;
		}

		// Prepare layout.
		if ( empty( $layout ) ) {
			$layout = 'form';
		}

		// Generate nonce and link to export content.
		global $pagenow;

		if ( 'post.php' == $pagenow && isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] ) {
			$nonce = wp_create_nonce( 'wr-export-post-content' );
			$elink = add_query_arg(
				array( 'action' => 'wr-export-post-content', 'nonce' => $nonce ),
				site_url()
			);
		}

		// Generate nonce and link to import preset.
		$nonce = wp_create_nonce( 'wr-import-content-preset' );
		$ilink = add_query_arg(
			array( 'action' => 'wr-import-content-preset', 'nonce' => $nonce ),
			site_url()
		);

		// Get content presets.
		$presets = self::get_content_presets();

		// Enqueue necessary scripts.
		wp_enqueue_media();
		wp_enqueue_script( 'wr-import-content', WR_CM_URL . 'assets/actions.js' );

		if ( 'full' == $layout ) :
		?>
		<div class="wr-content-migration box-wrap demos three-col">
			<?php
			foreach ( $presets as $preset => $define ) :
				$title = ucwords( implode( ' ', preg_split( '/[^\w]+/', $preset ) ) );
				$thumb = ( isset( $define['thumb'] ) && ! empty( $define['thumb'] ) )
					? $define['thumb']
					: 'https://placeholdit.imgix.net/~text?txtsize=26&txt=275%C3%97172&w=275&h=172'
			?>
			<div class="col">
				<div class="box">
					<img src="<?php echo esc_url( $thumb ); ?>">
					<div class="box-info">
						<h5><?php echo esc_html( $title ); ?></h5>
						<div>
							<a class="start-import button button-primary" href="<?php
								echo esc_url( add_query_arg( array( 'preset' => $preset ), $ilink ) );
							?>" title="<?php
								esc_attr_e( 'Import content preset', 'wr-nitro' );
							?>">
								<?php esc_html_e( 'Import', 'wr-nitro' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php elseif ( 'form' == $layout ) : ?>
		<div class="wr-content-migration" style="text-align: center;">
			<?php if ( 'post.php' == $pagenow && isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] ) : ?>
			<a class="export button" href="<?php echo esc_url( $elink ); ?>">
				<?php _e( 'Export content', WR_CM ); ?>
			</a>
			<hr />
			<?php endif; ?>
			<a class="import button" href="<?php echo esc_url( $ilink ); ?>">
				<?php _e( 'Import content', WR_CM ); ?>
			</a>
			<div class="wr-import-content-preset hidden">
				<?php if ( count( $presets ) ) : ?>
				<select class="wr-select-content-preset">
					<option value=""><?php _e( '- Select content preset -', WR_CM ); ?></option>
					<?php
					foreach ( array_keys( $presets ) as $preset ) :
						$title = ucwords( implode( ' ', preg_split( '/[^\w]+/', $preset ) ) );
					?>
					<option value="<?php echo esc_attr( $preset ); ?>"><?php echo esc_html( $title ); ?></option>
					<?php endforeach; ?>
				</select>
				<br />
				<?php _e( '- or -', WR_CM ); ?>
				<?php endif; ?>
				<input type="hidden" class="wr-select-exported-file" />
				<div class="wr-upload-exported-file">
					<p class="selected-file"></p>
					<a class="select-file" href="javascript:void(0)">
						<?php _e( 'Select exported file', WR_CM ); ?>
					</a>
					<br />
					<a class="remove-file hidden" href="javascript:void(0)">
						<?php _e( 'Remove selected file', WR_CM ); ?>
					</a>
				</div>
				<button type="button" class="start-import button" disabled="disabled">
					<?php _e( 'Import Content Preset', WR_CM ); ?>
				</button>
			</div>
		</div>
		<?php
		endif;
	}

	/**
	 * Method to export post content.
	 *
	 * @return  void
	 */
	public static function export() {
		// Check if this is a request to export content.
		if ( isset( $_REQUEST['action'] ) && 'wr-export-post-content' == $_REQUEST['action'] ) {
			// Get request variables.
			$nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : null;
			$id    = isset( $_GET['id'   ] ) ? $_GET['id'   ] : null;

			// Verify request variables.
			if ( ! wp_verify_nonce( $nonce, 'wr-export-post-content' ) || empty( $id ) ) {
				die( __( 'Invalid request.', WR_CM ) );
			}

			// Export content to XML document.
			$xml = simplexml_load_string( '<?xml version="1.0" encoding="UTF-8"?><export/>' );

			foreach ( ( array ) $id as $post ) {
				// Get post and meta data.
				$meta = get_post_meta( $post );
				$post = get_post( $post, ARRAY_A );

				// Add post to XML document.
				$node  = $xml->addChild( 'post' );
				$links = array();

				foreach ( array( 'content', 'title', 'excerpt', 'status', 'name', 'type', 'mime_type' ) as $k ) {
					if ( empty( $post[ "post_{$k}" ] ) || preg_match( '/^[\d\w]+$/', $post[ "post_{$k}" ] ) ) {
						$node->addChild( "post_{$k}", $post[ "post_{$k}" ] );
					} else {
						$child = $node->addChild( "post_{$k}" );

						$child->addAttribute( 'xml:space', 'preserve', 'xml' );

						$child = dom_import_simplexml( $child );
						$cdata = $child->ownerDocument->createCDataSection( $post[ "post_{$k}" ] );

						$child->appendChild( $cdata );

						// Retrieve linked content.
						self::export_linked_contents( $post[ "post_{$k}" ], $links );
					}
				}

				// Add meta data to XML document.
				$child = $node->addChild( 'post_meta' );

				foreach ( $meta as $k => $a ) {
					foreach ( $a as $v ) {
						if ( empty( $v ) || preg_match( '/^[\d\w]+$/', $v ) ) {
							$child->addChild( $k, $v );
						} else {
							$tmp = $child->addChild( $k );

							$tmp->addAttribute( 'xml:space', 'preserve', 'xml' );

							$tmp = dom_import_simplexml( $tmp );
							$cdata = $tmp->ownerDocument->createCDataSection( $v );

							$tmp->appendChild( $cdata );
						}
					}
				}

				// Add data of linked contents.
				if ( count( $links ) ) {
					$child = $node->addChild( 'linked_contents' );

					$child->addAttribute( 'xml:space', 'preserve', 'xml' );

					$child = dom_import_simplexml( $child );
					$cdata = $child->ownerDocument->createCDataSection( json_encode( $links ) );

					$child->appendChild( $cdata );
				}
			}

			// Apply a filter to allow 3rd-party to hook in.
			$xml = apply_filters( 'wr-content-migration-export-content', $xml, $post );

			// Stringify SimpleXMLElement object.
			$xml = trim( $xml->asXML() );

			// Clear all output buffering.
			while ( ob_get_level() ) {
				ob_end_clean();
			}

			// Send inline download header.
			header( 'Content-Type: text/xml; charset=utf-8'                               );
			header( 'Content-Length: ' . strlen( $xml )                                   );
			header( "Content-Disposition: attachment; filename={$post['post_title']}.xml" );
			header( 'Cache-Control: no-cache, must-revalidate, max-age=60'                );
			header( 'Expires: Sat, 01 Jan 2000 12:00:00 GMT'                              );

			// Print XML document.
			echo '' . $xml;

			// Exit immediately to prevent WordPress from processing further.
			exit;
		}
	}

	/**
	 * Method to import content preset.
	 *
	 * @return  void
	 */
	public static function import() {
		// Check if this is a request to import content.
		if ( isset( $_REQUEST['action'] ) && 'wr-import-content-preset' == $_REQUEST['action'] ) {
			// Get request variables.
			$nonce  = isset( $_GET['nonce' ] ) ? $_GET['nonce' ] : null;
			$preset = isset( $_GET['preset'] ) ? $_GET['preset'] : null;
			$id     = isset( $_GET['id'    ] ) ? $_GET['id'    ] : null;

			// Verify request variables.
			if ( ! wp_verify_nonce( $nonce, 'wr-import-content-preset' ) || empty( $preset ) ) {
				wp_send_json_error( __( 'Invalid request.', WR_CM ) );
			}

			if ( preg_match( '#^https?://#', $preset ) ) {
				$source = $preset;
				$preset = substr( basename( $preset ), 0, -4 );
			} else {
				// Get content presets.
				$presets = self::get_content_presets();

				// Make sure preset exists.
				if ( ! isset( $presets[ $preset ] ) ) {
					wp_send_json_error( __( 'Not found the selected content preset.', WR_CM ) );
				}

				// Get the selected content preset.
				$source = $presets[ $preset ]['source'];
			}

			if ( ! preg_match( '#^https?://#', $source ) ) {
				if ( ! is_file( $source ) ) {
					wp_send_json_error( __( 'Not found the selected content preset.', WR_CM ) );
				}

				// Read content from preset file.
				$source = file_get_contents( $source );

				if ( ! $source ) {
					wp_send_json_error( __( 'Failed to read content from the selected preset.', WR_CM ) );
				}
			} else {
				// Get content preset.
				$source = wp_remote_get( $source );

				if ( is_wp_error( $source ) ) {
					wp_send_json_error( $source->get_error_message() );
				}

				$source = $source['body'];
			}

			// Parse content preset.
			$xml = simplexml_load_string( trim( $source ) );

			if ( ! $xml ) {
				wp_send_json_error( __( 'Invalid content preset.', WR_CM ) );
			}

			foreach ( $xml->xpath( '//export/post' ) as $post ) {
				// Process linked contents.
				$links = array();

				if ( isset( $post->linked_contents ) ) {
					$links = json_decode( ( string ) $post->linked_contents, true );

					self::import_linked_contents( $links );
				}

				// Prepare post properties.
				$post_props = array();

				foreach( $post->children() as $post_prop ) {
					if ( 'post_meta' == $post_prop->getName() ) {
						$post_meta = $post_prop;
					} else {
						if ( ( string ) $post_prop == '' || preg_match( '/^[\d\w]+$/', ( string ) $post_prop ) ) {
							$post_props[ $post_prop->getName() ] = ( string ) $post_prop;
						} else {
							$post_props[ $post_prop->getName() ] = self::update_shortcodes( ( string ) $post_prop, $links );
						}
					}
				}

				// If editing a post, import to the current post.
				if ( isset( $id ) && ! empty( $id ) ) {
					$post_props['ID'] = $id;

					unset( $id );
				}

				// Use preset title as post title.
				if ( is_string( $preset ) ) {
					$post_props['post_title'] = ucwords( implode( ' ', preg_split( '/[^\w]+/', $preset ) ) );
				}

				// Insert post content.
				$insert_id = wp_insert_post( $post_props );

				if ( ! $insert_id || is_wp_error( $insert_id ) ) {
					wp_send_json_error(
						$insert_id ? $insert_id->get_error_message() : __( 'Failed to import content preset.', WR_CM )
					);
				}

				// Insert post meta as well.
				if ( isset( $post_meta ) ) {
					// Clear current post meta.
					global $wpdb;

					$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id = {$insert_id};" );

					// Then, import post meta from content preset.
					foreach ( $post_meta->children() as $meta ) {
						add_post_meta( $insert_id, $meta->getName(), ( string ) $meta );
					}
				}

				// Do an action to allow 3rd-party to hook in.
				do_action( 'wr-content-migration-import-content', $post, $insert_id );
			}

			wp_send_json_success( $insert_id );
		}
	}

	/**
	 * Register supported MIME types.
	 *
	 * @param   array  $t  Mime types keyed by the file extension regex corresponding to
	 *                     those types. 'swf' and 'exe' removed from full list. 'htm|html' also
	 *                     removed depending on '$user' capabilities.
	 *
	 * @return  array
	 */
	public static function upload_mimes( $t ) {
		// Add file extension and MIME type for XML.
		$t['xml'] = 'text/xml';

		return $t;
	}

	/**
	 * Export all linked contents (category, attachment, etc.).
	 *
	 * @param   string  $content  Original content.
	 * @param   array   &$links   Reference to linked contents.
	 *
	 * @return  void
	 */
	protected function export_linked_contents( $content, &$links ) {
		// Parse shortcodes.
		if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				if ( ! empty( $match[5] ) && preg_match( '/' . get_shortcode_regex() . '/', $match[5] ) ) {
					self::export_linked_contents( $match[5], $links );
				}

				else {
					$tag  = $match[2];
					$atts = shortcode_parse_atts( $match[3] );

					switch ( $tag ) {
						case 'rev_slider_vc' :
							if ( isset( $atts['alias'] ) && ! empty( $atts['alias'] ) ) {
								// Get Slider Revolution slider data.
								global $wpdb;

								$slider = $wpdb->get_row(
									"SELECT * FROM {$wpdb->prefix}revslider_sliders WHERE alias = '{$atts['alias']}';",
									ARRAY_A
								);

								$slides = $wpdb->get_results(
									"SELECT * FROM {$wpdb->prefix}revslider_slides WHERE slider_id = {$slider['id']};",
									ARRAY_A
								);

								$static_slides = $wpdb->get_results(
									"SELECT * FROM {$wpdb->prefix}revslider_static_slides WHERE slider_id = {$slider['id']};",
									ARRAY_A
								);

								$links['rev_slider_vc']['alias'][ $atts['alias'] ] = array(
									'slider'        => $slider,
									'slides'        => $slides,
									'static_slides' => $static_slides,
								);
							}
						break;

						case 'nitro_product_category' :
							if ( isset( $atts['cat_id'] ) && ! empty( $atts['cat_id'] ) ) {
								// Get WooCommerce product category.
								$cat = get_term( $atts['cat_id'], 'product_cat', ARRAY_A );

								// Get category thumbnail.
								$thumb = get_woocommerce_term_meta( $atts['cat_id'], 'thumbnail_id', true );

								if ( $thumb ) {
									$cat['thumb'] = get_post( $thumb, ARRAY_A );
									$cat['thumb']['meta'] = get_post_meta( $thumb );
								}

								$links['nitro_product_category']['cat_id'][ $atts['cat_id'] ] = $cat;
							}
						break;

						case 'nitro_banner' :
							if ( isset( $atts['image'] ) && ! empty( $atts['image'] ) ) {
								// Get attachment.
								$img = get_post( $atts['image'], ARRAY_A );
								$img['meta'] = get_post_meta( $atts['image'] );

								$links['nitro_banner']['image'][ $atts['image'] ] = $img;
							}
						break;

						case 'nitro_products' :
							if ( isset( $atts['ids'] ) && ! empty( $atts['ids'] ) ) {
								// Get WooCommerce products.
								foreach ( explode( ',', $atts['ids'] ) as $product_id ) {
									$product_id = trim( $product_id );
									$product = get_post( $product_id, ARRAY_A );
									$product['meta'] = get_post_meta( $product_id );

									// Get category thumbnail.
									if ( has_post_thumbnail( $product_id ) ) {
										$thumb = get_post_thumbnail_id( $product_id );

										$product['thumb'] = get_post( $thumb, ARRAY_A );
										$product['thumb']['meta'] = get_post_meta( $thumb );
									}

									$links['nitro_products']['ids'][ $atts['ids'] ][ $product_id ] = $product;
								}
							}
						break;
					}
				}
			}
		}
	}

	/**
	 * Import all linked contents (category, attachment, etc.).
	 *
	 * @param   array  &$links  Reference to linked contents.
	 *
	 * @return  void
	 */
	protected static function import_linked_contents( &$links ) {
		// Process linked contents.
		foreach ( $links as $tag => $atts ) {
			switch ( $tag ) {
				case 'rev_slider_vc' :
					foreach ( $atts as $att => $arr ) {
						if ( 'alias' == $att ) {
							foreach ( $arr as $alias => $data ) {
								// Check if slider already exists.
								global $wpdb;

								// Create new slider.
								unset( $data['slider']['id'] );

								$data['slider']['alias'] = $alias . '-' . wp_generate_password( 8, false );

								$wpdb->insert( "{$wpdb->prefix}revslider_sliders", $data['slider'] );

								$slider = $wpdb->insert_id;

								// Import slides.
								foreach ( $data['slides'] as $slide ) {
									unset( $slide['id'] );

									$slide['slider_id'] = $slider;

									$wpdb->insert( "{$wpdb->prefix}revslider_slides", $slide );
								}

								// Import static slides.
								foreach ( $data['static_slides'] as $slide ) {
									unset( $slide['id'] );

									$slide['slider_id'] = $slider;

									$wpdb->insert( "{$wpdb->prefix}revslider_static_slides", $slide );
								}

								$links[ $tag ][ $att ][ $alias ] = $data['slider']['alias'];
							}
						}
					}
				break;

				case 'nitro_product_category' :
					foreach ( $atts as $att => $arr ) {
						if ( 'cat_id' == $att ) {
							foreach ( $arr as $id => $data ) {
								// Create new category.
								$category = wp_insert_term( $data['name'], 'product_cat' );

								if ( ! $category ) {
									$links[ $tag ][ $att ][ $id ] = '';
								} else {
									if ( is_wp_error( $category ) ) {
										$cat_id = $category->get_error_data( 'term_exists' );
									} else {
										$cat_id = $category['term_id'];
									}

									$links[ $tag ][ $att ][ $id ] = $cat_id;

									// Download category thumbnail if has.
									if ( isset( $data['thumb'] ) ) {
										$data = self::import_attachment_image( $data['thumb'] );

										if ( $data ) {
											// Set category thumbnail.
											update_woocommerce_term_meta( $cat_id, 'thumbnail_id', $data );
										}
									}
								}
							}
						}
					}
				break;

				case 'nitro_banner' :
					foreach ( $atts as $att => $arr ) {
						if ( 'image' == $att ) {
							foreach ( $arr as $id => $data ) {
								// Download attachment image.
								$data = self::import_attachment_image( $data );

								if ( $data ) {
									$links[ $tag ][ $att ][ $id ] = $data;
								} else {
									$links[ $tag ][ $att ][ $id ] = '';
								}
							}
						}
					}
				break;

				case 'nitro_products' :
					foreach ( $atts as $att => $arr ) {
						if ( 'ids' == $att ) {
							foreach ( $arr as $ids => $products ) {
								foreach ( $products as $id => $data ) {
									// Create new product.
									unset( $data['ID'] );

									if ( isset( $data['meta'] ) ) {
										$meta = $data['meta'];

										unset( $data['meta'] );
									}

									if ( isset( $data['thumb'] ) ) {
										$thumb = $data['thumb'];

										unset( $data['thumb'] );
									}

									$product = wp_insert_post( $data );

									if ( ! $product || is_wp_error( $product ) ) {
										$links[ $tag ][ $att ][ $ids ][ $id ] = false;
									} else {
										$links[ $tag ][ $att ][ $ids ][ $id ] = $product;

										// Add post meta as well.
										if ( isset( $meta ) ) {
											foreach ( $meta as $k => $a ) {
												foreach ( $a as $v ) {
													add_post_meta( $product, $k, $v );
												}
											}
										}

										// Download product thumbnail if has.
										if ( isset( $thumb ) ) {
											$thumb = self::import_attachment_image( $thumb );

											if ( $thumb ) {
												// Set product thumbnail.
												set_post_thumbnail( $product, $thumb );
											}
										}
									}
								}

								$links[ $tag ][ $att ][ $ids ] = implode( ', ', array_filter( $links[ $tag ][ $att ][ $ids ] ) );
							}
						}
					}
				break;
			}
		}
	}

	/**
	 * Update linked contents in shortcodes.
	 *
	 * @param   string  $content  Original content.
	 * @param   array   $links    Linked contents.
	 *
	 * @return  string
	 */
	protected static function update_shortcodes( $content, $links ) {
		if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				if ( ! empty( $match[5] ) && preg_match( '/' . get_shortcode_regex() . '/', $match[5] ) ) {
					$content = str_replace( $match[5], self::update_shortcodes( $match[5], $links ), $content );
				}

				else {
					$tag  = $match[2];
					$atts = shortcode_parse_atts( $match[3] );

					switch ( $tag ) {
						case 'rev_slider_vc' :
							if ( isset( $atts['alias'] ) && ! empty( $atts['alias'] ) ) {
								$content = str_replace(
									$match[0],
									str_replace(
										' alias="' . $atts['alias'] . '"',
										' alias="' . $links['rev_slider_vc']['alias'][ $atts['alias'] ] . '"',
										$match[0]
									),
									$content
								);
							}
						break;

						case 'nitro_product_category' :
							if ( isset( $atts['cat_id'] ) && ! empty( $atts['cat_id'] ) ) {
								$content = str_replace(
									$match[0],
									str_replace(
										' cat_id="' . $atts['cat_id'] . '"',
										' cat_id="' . $links['nitro_product_category']['cat_id'][ $atts['cat_id'] ] . '"',
										$match[0]
									),
									$content
								);
							}
						break;

						case 'nitro_banner' :
							if ( isset( $atts['image'] ) && ! empty( $atts['image'] ) ) {
								$content = str_replace(
									$match[0],
									str_replace(
										' image="' . $atts['image'] . '"',
										' image="' . $links['nitro_banner']['image'][ $atts['image'] ] . '"',
										$match[0]
									),
									$content
								);
							}
						break;

						case 'nitro_products' :
							if ( isset( $atts['ids'] ) && ! empty( $atts['ids'] ) ) {
								$content = str_replace(
									$match[0],
									str_replace(
										' ids="' . $atts['ids'] . '"',
										' ids="' . $links['nitro_products']['ids'][ $atts['ids'] ] . '"',
										$match[0]
									),
									$content
								);
							}
						break;
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Import attachment image.
	 *
	 * @param   array  $data  Attachment image data.
	 *
	 * @return  int
	 */
	protected static function import_attachment_image( $data ) {
		$image = download_url( $data['guid'] );

		if ( ! is_wp_error( $image ) ) {
			$image = array(
				'name'     => basename( $data['guid'] ),
				'tmp_name' => $image,
			);

			$image = wp_handle_sideload( $image, array( 'test_form' => false ) );

			if ( ! isset( $image['error'] ) ) {
				$file = $image['file'];
				$meta = $data['meta'];

				unset( $data['ID'] );
				unset( $data['meta'] );

				$data['guid'] = $image['url'];

				$image = wp_insert_post( $data );

				if ( $image && ! is_wp_error( $image ) ) {
					// Add post meta as well.
					foreach ( $meta as $k => $a ) {
						foreach ( $a as $v ) {
							if ( '_wp_attachment_metadata' == $k ) {
								$v = maybe_unserialize( $v );

								// Update local file path.
								$v['file'] = array_pop( explode( '/uploads/', $data['guid'] ) );

								if ( isset( $v['sizes'] ) ) {
									foreach ( $v['sizes'] as $size => $define ) {
										if ( isset( $define['url'] ) ) {
											// Generate thumbnail.
											$thumb = image_resize( $file, $define['width'], $define['height'] );

											if ( $thumb ) {
												$search = current( explode( '/uploads/', $thumb ) );
												$replace = current( explode( '/uploads/', $data['guid'] ) );

												$v['sizes'][ $size ]['url'] = str_replace( $search, $replace, $thumb );
											}
										}

										if ( isset( $define['file'] ) ) {
											$v['sizes'][ $size ]['file'] = basename( $v['sizes'][ $size ]['url'] );
										}

										if ( isset( $define['path'] ) ) {
											$v['sizes'][ $size ]['path'] = array_pop(
												explode( '/uploads/', $v['sizes'][ $size ]['url'] )
											);
										}
									}
								}
							}

							else if ( '_wp_attached_file' == $k ) {
								$v = array_pop( explode( '/uploads/', $data['guid'] ) );
							}

							add_post_meta( $image, $k, $v );
						}
					}

					return $image;
				}
			}
		}
	}

	/**
	 * Get available content presets.
	 *
	 * @return  array
	 */
	protected static function get_content_presets() {
		// Get all XML file in 'presets' folder.
		$files   = glob( WR_CM_PATH . 'presets/*.xml' );
		$presets = array();

		foreach ( $files as $file ) {
			// Look for preset thumbnail.
			$preset = substr( basename( $file ), 0, -4 );
			$thumb  = dirname( $file ) . "/{$preset}.jpg";

			$presets[ $preset ] = array(
				'thumb'  => is_file( $thumb ) ? $thumb : null,
				'source' => $file,
			);
		}

		return apply_filters( 'wr-content-migration-presets', $presets );
	}
}
