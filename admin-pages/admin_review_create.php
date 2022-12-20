<?php
/**
 * Created by PhpStorm.
 * User: jamesmcdermott
 * Date: 12/03/20
 * Time: 7:00 PM
 */
! defined( 'ABSPATH' ) && exit;

//add_action(
//	'init',
//	array( DRAdminReviewManager::get_instance(), 'plugin_setup' )
//);

class DRAdminReviewCreateOld {
	/**
	 * Settings pages associated with add-ons
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @var array $addon_pages
	 */
	public static $addon_pages = array();

	/**
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFSettings::$addon_pages
	 *
	 * @param string|array $name      The settings page slug.
	 * @param string|array $handler   The callback function to run for this settings page.
	 * @param string       $icon_path The path to the icon for the settings tab.
	 */
	public static function add_options_page( $name, $handler, $icon_path ) {
		$title = '';

		// if name is an array, assume that an array of args is passed
		if ( is_array( $name ) ) {

			extract(
				wp_parse_args(
					$name, array(
						'name'      => '',
						'title'     => '',
						'tab_label' => '',
						'handler'   => false,
						'icon_path' => '',
					)
				)
			);

		}

		if ( ! isset( $tab_label ) || ! $tab_label ) {
			$tab_label = $name;
		}

		/**
		 * Adds additional actions after settings pages are registered.
		 *
		 * @since Unknown
		 *
		 * @param string|array $handler The callback function being run.
		 */
		add_action( 'dresc_options_' . str_replace( ' ', '_', $name ), $handler );
		self::$addon_pages[ $name ] = array( 'name' => $name, 'title' => $title, 'tab_label' => $tab_label, 'icon' => $icon_path );
	}

	/**
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses GFSettings::get_subview()
	 * @uses GFSettings::dr_escapiawp_options_page()
	 * @uses GFSettings::settings_uninstall_page()
	 * @uses GFSettings::page_header()
	 * @uses GFSettings::page_footer()
	 *
	 * @return void
	 */
	public static function settings_page() {

		$subview = self::get_subview();

		switch ( $subview ) {
			case 'options':
				self::dr_escapia_review_create_page();
				break;
			case 'options':
				self::dr_escapia_review_create_page();
				break;
			case 'uninstall':
				self::settings_uninstall_page();
				break;
			default:
				self::page_header();

				/**
				 * Fires in the settings page depending on which page of the settings page you are in (the Subview).
				 *
				 * @since Unknown
				 *
				 * @param mixed $subview The sub-section of the main Form's settings
				 */
				do_action( 'dresc_settings_' . str_replace( ' ', '_', $subview ) );
				self::page_footer();
		}
	}

	public static function dr_escapia_review_create_page() {

		$dr_escapia_options = get_option( 'dr_escapia_options' );
		DREscapiaSettings::page_header( __( 'Create Review', 'dresc-plugin' ), '' );

		global $wpdb;
		$reservationNumber = '';
		$status='';
		$message='';

		if(isset($_GET['reservation_number'])){
			$reservationNumber = $_GET['reservation_number'];
		}

		if(isset($_POST['reservation_number'])) {
			$reservationNumber = $_GET['reservation_number'];
		}

//		$reviewCheck = $wpdb->get_row('select * from '.DRECModel::get_unit_reviews_table_name().' where reservation_number="'. $reservationNumber .'"');
//
//		if(isset($reviewCheck)){
//			$status='defined';
//			$message='A review for this stay has already been submitted';
//		}

		if(isset($_POST['reservation_number'])){


			$wpdb->insert(
				DRECModel::get_unit_reviews_table_name(),
				[
					'property_id'=>$_GET['reservation_number'],
					'review_date'=>date('Y-m-d H:i:s'),
					'reviewer_title'=>$_POST['review_title'],
					'reviewer_comment' => $_POST['review_comment'],
					'reviewer_name' => $_POST['reviewer_name'],
					'check_in' => $_POST['arrival_date'],
					'check_out' => $_POST['departure_date'],
					'reservation_number' => $_POST['reservation_number'],
					'property_id' => $_POST['property_id'],
					'review_status' => 1,
					'enabled' => false
				]

			);

			$reviewId = $wpdb->insert_id;

			$wpdb->insert(
				DRECModel::get_unit_review_scores_table_name(),
				[
					'review_id'=>$reviewId,
					'score' => $_POST['review_score'],
					'category_id' => '1000',
					'sort_order' => 1
				]
			);

			$status='submitted';
			$message='Review Created';

		}

		if($reservationNumber!=''){
			global $wpdb;
			$reservationDetail = $wpdb->get_row("select reservation_id, reservation_type, escapia_id, arrival_date, departure_date, escapia_id, reservation_status, email, first_name, last_name, review_request_status from "
			                                    .DRECModel::get_escapia_reservations_table_name()." as reservation 
	                                    INNER JOIN ". DRECModel::get_escapia_customers_table_name()." as customer  on customer.id = reservation.customer_id 
										WHERE reservation_number='".$reservationNumber."'");

			$property = $wpdb->get_row("SELECT * from ". DRECModel::get_properties_table_name(). " where escapia_id='".$reservationDetail->escapia_id."'");


		}
		?>
        <link rel="stylesheet" id="dr_escapia_wp_admin-css" href="<?php echo content_url();?>/plugins/dr-escapia/css/admin.min.css?ver=2.4.14" type="text/css" media="all">

        <link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri();?>/fontawesome/css/all.min.css">
<!--        <link rel="stylesheet" type="text/css" href="--><?php //echo get_stylesheet_directory_uri();?><!--/css/vrm-style.css">-->
<!--        <link rel="stylesheet" type="text/css" href="--><?php //echo get_stylesheet_directory_uri();?><!--/css/vrm-book.css">-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
        <style>

            .row{
                text-align: left;
            }

            /*#rateYo{*/
            /*    display: inline-block !important;*/
            /*}*/

            input,  select,  textarea {
                width: 100% !important;
            }
            input,  select,  textarea {
                width: 100%;
            }
            input,  input, -mobile input, select,  select, textarea {
                /* width: 100%; */
                padding: 0 1em;
                border: 2px solid #f0f1f2;
                border-radius: 4px;
                background-color: #ffffff;
                font-size: 18px;
                font-size: 1.7rem;
                line-height: 2;
            }

        </style>
        <div id="scroller-anchor" class="scroller-anchor"></div>

        <div id="vrm-wrap" class="vrm-wrap">
            <div class="property-search-bar property-search-bar-main alignfull">
				<?php if ( $message!='' ) { ?>
                    <div id="message" class="updated"><p><?php echo $message; ?></p></div>
				<?php } ?>

                <form data-abide id="frmBooking" name="frmBooking" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="POST">
                    <input type="hidden" name="reservation_number" name="reservation_number" value="<?php echo $reservationNumber; ?>">
<!--                    <input type="hidden" name="arrival_date" value="--><?php //echo $reservationDetail->arrival_date; ?><!--">-->
                    <input type="hidden" name="departure_date" value="<?php echo $reservationDetail->departure_date; ?>">
                    <input type="hidden" name="escapia_id" value="<?php echo $reservationDetail->escapia_id; ?>">
                    <input type="hidden" name="property_id" value="<?php echo $property->id; ?>">
                    <section class="booking-contact-info">
                        <h1>Review For Stay at <?php echo $property->short_description; ?> FROM <?php echo $reservationDetail->arrival_date; ?> TO <?php echo $reservationDetail->departure_date; ?></h1>

                        <fieldset class="contact-information" style="max-width: 800px; margin: auto">
                            <legend>Contact Information</legend>
                            <div class="row">
                                <div class="large-8 columns">
                                    <label>Review Title
                                        <input type="text" id="review_title" name="review_title" placeholder="Review Title" required=" Name Is Required"  />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-8 columns">
                                    <label>Arrival Date
                                        <input type="text" id="arrival_date" name="arrival_date" placeholder="MM/DD/YYYY" required=" Arrival Date Is Required"  />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-8 ">
                                    <label>Rating

                                        <div id="rateYo"></div>
                                        <input type="hidden" name="review_score" id="review_score">
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-8 columns">
                                    <label>Name (First name, last initial - i.e: Jim M.)
                                        <input type="text" id="reviewer_name" name="reviewer_name" placeholder="Customer Name" required=" Name Is Required"  />
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-8 columns">
                                    <label>Comments
                                        <textarea type="text" id="review_comment" name="review_comment"  class="regular-text" rows="5" cols="50" ></textarea>
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="large-4 columns text-center">
                                    <button type="submit" name="btnCCPayment"  class="button">SUBMIT REVIEW</button>
                                </div>
                            </div>
                        </fieldset>






            </div>
            </section>
            </form>

        </div>
        </div>

        <div class="vrm-load-wrap">
            Loading....<br>
            <div class="vrm-loading-screen">

            </div>
        </div>
        <script type='text/javascript' src='<?php echo get_stylesheet_directory_uri();?>/js/vrm-global.js'></script>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
        <script>
            window.onload = function () {
                hideLoad();

                // var ratingOptions = {
                //     // max_value: 5,
                //     // step_size: 1,
                //     // initial_value: 0,
                //     // update_input_field_name: jQuery("#review_score"),
                // }
                // jQuery(".rating").rate(ratingOptions);


                // jQuery('.rateit').rateit();


                jQuery("#rateYo").rateYo({
                    rating: 0,
                    fullStar: true
                });


                jQuery("#rateYo").rateYo()
                    .on("rateyo.set", function (e, data) {

                        var rating = data.rating;
                        console.log(rating);
                        jQuery('#review_score').val(rating);
                    });
                // jQuery("#rating").click(function(){
                //     $("span#value").html($('#rating').rateit('value'));
                // });

            }
        </script>
		<?php
		self::page_footer();
	}

	public static function enableReviews(){

		global $wpdb;


		foreach($_POST['selected-reviews'] as $index => $value) {

			$reviewId = $_POST['selected-reviews'][$index];

			$review= $wpdb->get_row('select * from '.DRECModel::get_unit_reviews_table_name().' where id="'.$reviewId.'"');

			DRAdminReviewCreate::updateReviewStatus($reviewId, true, 1);

		}


	}

	public static function deleteReviews(){

		global $wpdb;


		foreach($_POST['selected-reviews'] as $index => $value) {

			$reviewId = $_POST['selected-reviews'][$index];

			$review= $wpdb->get_row('delete from '.DRECModel::get_unit_reviews_table_name().' where id="'.$reviewId.'"');

		}
	}

	public static function disableReviews(){

		global $wpdb;
		$post_status='draft';
		foreach($_POST['selected-reviews'] as $index => $value) {
			$reviewId = $_POST['selected-reviews'][ $index ];
			$review= $wpdb->get_row('select * from '.DRECModel::get_unit_reviews_table_name().' where id="'.$reviewId.'"');
			DRAdminReviewCreate::updateReviewStatus($reviewId, false, 2 );
		}
	}

	public static function updateReviewStatus($reviewId, $enabled, $status){

		global $wpdb;

		$wpdb->update(DRECModel::get_unit_reviews_table_name(),
			['enabled' =>$enabled,
			 'review_status' =>$status],
			['id'=>$reviewId]
		);

	}







	/**
	 * Outputs the settings page header.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @uses SCRIPT_DEBUG
	 * @uses GFSettings::get_subview()
	 * @uses GFSettings::$addon_pages
	 * @uses GFCommon::get_browser_class()
	 * @uses GFCommon::display_dismissible_message()
	 *
	 * @param string $title   Optional. The page title to be used. Defaults to an empty string.
	 * @param string $message Optional. The message to display in the header. Defaults to empty string.
	 *
	 * @return void
	 */
public static function page_header( $title = '', $message = '' ) {

	// Print admin styles.
	wp_print_styles( array( 'jquery-ui-styles', 'dresc_admin' ) );

	$current_tab = self::get_subview();

	// Build left side options, always have  Options first, Settings second, put add-ons iafter.
	$setting_tabs = array( '10' => array( 'name' => 'settings', 'label' => __( 'Reviews', 'dresc-plugin' ) ) );

	if ( ! empty( self::$addon_pages ) ) {

		$sorted_addons = self::$addon_pages;
		asort( $sorted_addons );

		// Add add-ons to menu
		foreach ( $sorted_addons as $sorted_addon ) {
			$setting_tabs[] = array(
				'name'  => urlencode( $sorted_addon['name'] ),
				'label' => esc_html( $sorted_addon['tab_label'] ),
				'title' => esc_html( rgar( $sorted_addon, 'title' ) ),
			);
		}
	}

	/**
	 * Filters the Settings menu tabs.
	 *
	 * @since Unknown
	 *
	 * @param array $setting_tabs The settings tab names and labels.
	 */
	$setting_tabs = apply_filters( 'dresc_settings_menu', $setting_tabs );
	ksort( $setting_tabs, SORT_NUMERIC );

	// Kind of boring having to pass the title, optionally get it from the settings tab
	if ( ! $title ) {
		foreach ( $setting_tabs as $tab ) {
			if ( $tab['name'] == urlencode( $current_tab ) ) {
				$title = ! empty( $tab['title'] ) ? $tab['title'] : $tab['label'];
			}
		}
	}

	?>

	<div class="wrap <?php echo DRESCCommon::get_browser_class() ?> dresc_settings_wrap">

		<?php if ( $message ) { ?>
			<div id="message" class="updated"><p><?php echo $message; ?></p></div>
		<?php } ?>

		<h2><?php echo esc_html( $title ) ?></h2>

		<?php DRESCCommon::display_dismissible_message(); ?>

		<div id="dresc_tab_group" class="dresc_tab_group vertical_tabs">
			<ul id="dresc_tabs" class="dresc_tabs">
				<?php
				foreach ( $setting_tabs as $tab ) {
					$name = $tab['label'];
					$url  = add_query_arg( array( 'subview' => $tab['name'] ), admin_url( 'admin.php?page=dr_settings' ) );
					?>
					<li <?php echo urlencode( $current_tab ) == $tab['name'] ? "class='active'" : '' ?>>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $tab['label'] ) ?></a>
					</li>
					<?php
				}
				?>
			</ul>

			<div id="dresc_tab_container" class="dresc_tab_container">
				<div class="dresc_tab_content" id="tab_<?php echo esc_attr( $current_tab ); ?>">

					<?php
					}

					/**
					 * Outputs the Settings page footer.
					 *
					 * @since  Unknown
					 * @access public
					 *
					 * @return void
					 */
					public static function page_footer() {
					?>
				</div>
				<!-- / dresc_tab_content -->
			</div>
			<!-- / dresc_tab_container -->
		</div>
		<!-- / dresc_tab_group -->

		<br class="clear" style="clear: both;" />

	</div> <!-- / wrap -->

	<?php
}

	/**
	 * Gets the Settings page subview based on the query string.
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @return string The subview.
	 */
	public static function get_subview() {

		// Default to subview, if no subview provided support
		$subview = rgget( 'subview' ) ? rgget( 'subview' ) : rgget( 'addon' );

		if ( ! $subview ) {
			$subview = 'review_manager';
		}

		return $subview;
	}





}