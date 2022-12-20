<?php
add_action(
	'init',
	array( DRAdminReviewCreate::get_instance(), 'plugin_setup' )
);
class DRAdminReviewCreate {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 */
	protected static $instance;
	protected $template;

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook admin_init
	 * @since   05/02/2013
	 */
	public static function get_instance() {

		null === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  admin_init
	 * @return   void
	 * @since    05/02/2013
	 */
	public function plugin_setup() {

		add_action( 'admin_menu', array( $this, 'register_submenu' ) );

	}

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {

	}


	public function register_submenu() {

		add_submenu_page(
			'admin.php?page=dr_escapia_review_upload',
			__( 'Create Review' ),
			__( 'Create Review' ),
			'manage_options',
			'dr_review_create',
			array( $this, 'dr_get_review_create_page' )
		);


	}



	/**
	 * Add Menu item on WP Backend
	 *
	 * @return void
	 * @since  0.0.1
	 * @uses   add_menu_page
	 * @access public
	 */
	public function add_menu_page() {


		$hook = add_submenu_page(
			'admin.php?page=dr_escapia_review_upload',
			__( 'Review Request EMail' ),
			__( 'Review Request EMail' ),
			'manage_options',
			'dr_review_request_email_template',
			array( $this, 'dr_get_review_create_page' )
		);


		error_log('registering scrxipts');

		add_action( 'load-' . $hook, array( $this, 'register_scripts' ) );
	}


	public function dr_get_review_create_page() {

//		$dr_escapia_options = get_option( 'dr_escapia_options' );
//		DREscapiaSettings::page_header( __( 'Create Review', 'dresc-plugin' ), '' );

		global $wpdb;
		$reservationNumber = '';
		$status='';
		$message='';


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
		$propertyTable = DRECModel::get_properties_table_name();
		$properties = $wpdb->get_results( 'select * from '.$propertyTable.' where active=1  order by short_description');
//        error_log($wpdb->num_rows);
//        error_log($propertyTable);
//        print_r('the properties');
////		print_r($properties, false);
//
//        foreach ($properties as $aProperty){
//            print_r($aProperty->short_description.'<br>');
//        }


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
                    <h1>Create New Review Entry</h1>
                    <input type="hidden" name="reservation_number" name="reservation_number" value="<?php echo $reservationNumber; ?>">
                    <!--                    <input type="hidden" name="arrival_date" value="--><?php //echo $reservationDetail->arrival_date; ?><!--">-->
                    <input type="hidden" name="departure_date" value="<?php echo $reservationDetail->departure_date; ?>">
                    <input type="hidden" name="escapia_id" value="<?php echo $reservationDetail->escapia_id; ?>">
                    <section class="booking-contact-info">

                        <fieldset class="contact-information" style="max-width: 800px; margin: auto">

                            <div class="row">
                                <div class="large-8 columns">
                                    <label>Property

                                        <select name="property_id" id="property_id" required style="max-width: 100% !important;font-size: 1.7rem">
                                            <option value="">Select A Property</option>
                                            <?php foreach($properties as $property) { ?>
                                                <option value="<?php echo $property->id ?>"><?php echo $property->short_description; ?></option>
                                            <?php } ?>
                                        </select>
                                    </label>
                                </div>
                            </div>
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

	}




	public function register_scripts() {

		wp_enqueue_script( 'jquery-ui-dialog' );

//		wp_enqueue_script( 'jquery-ui-demo', plugin_dir_url( __FILE__ ) . '../../js/email-template-builder.js',
//			array( 'jquery-ui-core' ) );

		wp_enqueue_style( 'jquery-ui-demo', plugin_dir_url( __FILE__ ) . '../../css/email-template-builder.css' );

		wp_enqueue_style( 'jquery-ui-css', plugin_dir_url( __FILE__ ) . '../../css/jquery-ui-fresh.css' );

		$base_url =DRESCCommon::get_base_url();
		$version= drEscapiaPlugin::$version;
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
//		wp_register_script( 'drec_tooltip_init', $base_url . "/js/tooltip_init.js", array( 'jquery-ui-tooltip' ), $version );
//		wp_register_style( 'drec_tooltip', $base_url . "/css/tooltip.css", array( 'drec_font_awesome' ), $version );
//		wp_register_style( 'drec_font_awesome', $base_url . "/css/font-awesome.css", null, $version );

	}

}