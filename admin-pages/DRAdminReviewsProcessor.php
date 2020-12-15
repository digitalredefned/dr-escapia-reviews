<?php
/**
 * Created by PhpStorm.
 * User: jamesmcdermott
 * Date: 11/19/20
 * Time: 7:00 PM
 */
! defined( 'ABSPATH' ) && exit;

add_action(
	'init',
	array( DRAdminReviewsProcessor::get_instance(), 'plugin_setup' )
);
class DRAdminReviewsProcessor {
	protected $admin_pages_dir = '';
	protected static $instance;


	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook admin_init
	 * @since   05/02/2013
	 */
	public static function get_instance() {

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}


	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  admin_init
	 * @since    05/02/2013
	 * @return   void
	 */
	public function plugin_setup() {

		add_action( 'admin_menu', array( $this, 'add_menu_page' ) , 11);

	}

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {

	}

	/**
	 * Add Menu item on WP Backend
	 *
	 * @uses   add_menu_page
	 * @access public
	 * @since  0.0.1
	 * @return void
	 */
	public function add_menu_page() {

		$page_hook_suffix = add_menu_page(
			esc_html__( 'Review Processor', 'drEscapiaManager' ),
			esc_html__( 'Review Processor', 'drEscapiaManager' ),
			'read',
			'dr_escapia_review_upload',
			array( $this, 'dr_escapia_review_import_page' ),
			'dashicons-admin-home'
		);

		add_submenu_page(
			'dr_escapia_review_upload',
			__( 'Review Request EMail' ),
			__( 'Review Request EMail' ),
			'manage_options',
			'dr_review_request_email_template',
			array( $this, 'dr_get_review_request_email_page' )
		);

//		//TODO: Add script action
	}


	public function dr_escapia_review_import_page() {

	    $message = '';

		if ( isset($_POST["submit"]) ) {

$storagename='';
			if ( isset($_FILES["csv_processor_form"])) {

				//if there was an error uploading the file
				if ($_FILES["csv_processor_form"]["error"] > 0) {
					echo "Return Code: " . $_FILES["csv_processor_form"]["error"] . "<br />";

				}
				else {
					//Print file details
					echo "Upload: " . $_FILES["csv_processor_form"]["name"] . "<br />";
					echo "Type: " . $_FILES["csv_processor_form"]["type"] . "<br />";
					echo "Size: " . ($_FILES["csv_processor_form"]["size"] / 1024) . " Kb<br />";
					echo "Temp file: " . $_FILES["csv_processor_form"]["tmp_name"] . "<br />";

					//if file already exists
					if (file_exists($_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/booking_import/" . $_FILES["csv_processor_form"]["name"])) {

						unlink($_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/booking_import/" . $_FILES["csv_processor_form"]["name"]);
						echo $_FILES["csv_processor_form"]["name"] . " already exists. ";
					}
					else {
						//Store file in directory "upload" with the name of "uploaded_file.txt"
						$storagename = "imports.csv";
						move_uploaded_file($_FILES["csv_processor_form"]["tmp_name"], $_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/booking_import/" . $storagename);
						echo "Stored in: " . content_url()."/uploads/booking_import/" . $_FILES["csv_processor_form"]["name"]. "<br />";
					}
				}
			} else {
				$message = 'No filed selected';
			}
		}

		if ( isset($storagename) && $file = fopen( $_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/booking_import/" . $storagename , r ) ) {


			$csv = array();

// check there are no errors

            $file = fopen($_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/booking_import/" . $storagename,"r");

            while (($data = fgetcsv($file)) !== FALSE)
            {
	            $resType = $data[1];
	            if($resType=='Renter'){

		            $dr_escapia_options = get_option( 'dr_escapia_options' );



                $connection = new EscapiaConnection();

	            $escapiaReservation = new EscapiaReservation($connection);
	            $reservationDetails = $escapiaReservation->reservationsByCustomerId($dr_escapia_options['escapia_account_number'].'-'.$data[14]);

                if(isset($reservationDetails)){
//                echo "email address " . $data[3] .'<br>';
                $resNumber = $data[16];
                $resEmail = $data[15];
                $reservationCreationDate = $data[5];



                if(gettype($reservationDetails->UnitReservations->UnitReservation)=='array'){

                        foreach ($reservationDetails->UnitReservations->UnitReservation as $unitReservation){


                            $this->processReservationDetails($unitReservation, $resNumber, $reservationCreationDate, $resEmail);
                        }

                }else{

                    $this->processReservationDetails($reservationDetails->UnitReservations->UnitReservation, $resNumber, $reservationCreationDate, $resEmail);
                }



            }
	            }
            }
            $message='File has been uploaded and reservations processed';
		}




		?>
		  <div class="wrap">
			  <?php if ( $message ) { ?>
                  <div id="message" class="updated"><p><?php echo $message; ?></p></div>
			  <?php } ?>
                <link rel="stylesheet" href="<?php echo content_url();?>/plugins/dr-escapia/js/tablesorter-master/css/theme.default.css">
<!--                <form method="post" action="" id="properties-form">-->


                    <div id="icon-options-general" class="icon32"></div>
                    <h1>Properties</h1>

                    <div id="poststuff">

                        <div id="post-body" class="metabox-holder columns-2">
	                        <?php if(isset($_GET['result']) && $_GET['result']=='property_deleted'){?>
                                <div class="notice notice-success inline">
                                    <p>
                                        Property Deleted</p>
                                </div>
	                        <?php }?>
                            <!-- main content -->
                            <div id="post-body-content">

                                <div class="meta-box-sortables ui-sortable">

                                    <div class="postbox">

                                        <h2><span>Upload File</span></h2>

                                        <div class="inside">

	                                        <table width="600">
		                                        <form id="csv_processor_form"  method="post" enctype="multipart/form-data">

			                                        <tr>
				                                        <td width="20%">Select file</td>
				                                        <td width="80%"><input type="file" name="csv_processor_form" id="file" /></td>
			                                        </tr>

			                                        <tr>
				                                        <td>Submit</td>
				                                        <td><input  type="submit" name="submit" value="Import Reservations" /></td>
			                                        </tr>

		                                        </form>
	                                        </table>




                                        </div>
                                        <!-- .inside -->

                                    </div>
                                    <!-- .postbox -->

                                </div>
                                <!-- .meta-box-sortables .ui-sortable -->

                            </div>
                            <!-- post-body-content -->

                            <!-- sidebar -->

                            <!-- #postbox-container-1 .postbox-container -->

                        </div>
                        <!-- #post-body .metabox-holder .columns-2 -->

                        <br class="clear">
                    </div>
                    <!-- #poststuff -->
<!--                </form>-->

            </div> <!-- .wrap -->
        <script>
            // jQuery("#csv_processor_form").submit(function(e){
            //
            //     e.preventDefault();
            //
            //     var file = document.getElementById("file");
            //
            //     var data = new FormData();
            //     data.append('file', file.files[0]);
            //     data.append('action','testingAjax');
            //
            //     var ajax = new XMLHttpRequest();
            //     ajax.open('post',ajaxurl);
            //     ajax.send(data);
            //
            // });
            //
            // jQuery("#file_upload_form").submit(function(e){
            //
            //     e.preventDefault();
            //
            //     var file = document.getElementById("video_browse");
            //
            //     var data = new FormData();
            //     data.append('video_browse', file.files[0]);
            //     data.append('action','reservation_uploading_ajax');
            //
            //     var ajax = new XMLHttpRequest();
            //     ajax.open('post',ajaxurl);
            //     ajax.send(data);
            //
            // });


        </script>
            <?php
	}

	private function processReservationDetails($unitReservation, $importReservationNumber, $reservationCreationDate, $resEmail){

		foreach($unitReservation->ResGlobalInfo->UnitReservationIDs->UnitReservationID as $reservation){


			$resID_Value =$reservation->ResID_Value;


			if (substr($resID_Value, 0, 3)== 'RES') {
				$reservationId= $resID_Value;
				$reservationNumber = $reservation->ResID_Value;
			}

			if ($reservation->ResID_Source=='EscapiaNET') {
				error_log('found escapia net res id');
				$reservationId = $reservation->ResID_Value;
			}

			if ($reservation->ResID_Source=='VRS' && isset($reservation->ForGuest) && $reservation->ForGuest=='true') {
				error_log('found confirmation code');
				$confirmationCode = $reservation->ResID_Value;
			}
		}

		if($importReservationNumber==$reservationNumber) {

			$customer['Email']       = $resEmail;
			$customer['GivenName']   = $unitReservation->ResGuests->ResGuest->Profiles->ProfileInfo->Profile->Customer->PersonName->GivenName;
			$customer['Surname']     = $unitReservation->ResGuests->ResGuest->Profiles->ProfileInfo->Profile->Customer->PersonName->Surname;
			$customer['PhoneNumber'] = $unitReservation->ResGuests->ResGuest->Profiles->ProfileInfo->Profile->Customer->Telephone->PhoneNumber;

			$reservationDetail['escapia_id']                = $unitReservation->UnitStays->UnitStay->BasicUnitInfo->UnitCode;
			$reservationDetail['reservation_type']          = 'Reservation';
			$reservationDetail['TotalAmount']               = $unitReservation->UnitStays->UnitStay->Total->AmountBeforeTax + $unitReservation->UnitStays->UnitStay->Total->Taxes->Amount;
			$reservationDetail['remaining_amount']          = '';
			$reservationDetail['remaining_amount_deadline'] = '';
			$reservationDetail['reservation_id']            = $reservationId;
			$reservationDetail['reservation_number']        = $reservationNumber;
			$reservationDetail['confirmation_code']         = $confirmationCode;
			$reservationDetail['reservation_status']        = $unitReservation->ResStatus;
			$reservationDetail['arrival_date']              = $unitReservation->UnitStays->UnitStay->TimeSpan->Start;
			$reservationDetail['departure_date']            = $unitReservation->UnitStays->UnitStay->TimeSpan->End;
			$reservationDetail['creation_date']            = $reservationCreationDate;



			$this->saveReservation($reservationDetail, $customer);


		}
	}


	private function saveReservation($reservation, $customer){

        global $wpdb;

        $reservationCheck = $wpdb->get_row('select * from '.DRECModel::get_escapia_reservations_table_name().' where reservation_number="'. $reservation['reservation_number'] .'"');

        if(isset($reservationCheck)){
            return $reservationCheck->id;
        }else{

		$customerId=$this->getOrSaveCustomer($customer);

		$amountRemainingDeadline='';
		if($reservation['remaining_deadline']!=''){
			$amountRemainingDeadline = date('Y-m-d', strtotime($reservation['remaining_deadline']));
		}

		$this->currentDate = date('Y-m-d H:i:s');



	        $phpdate = strtotime( $reservation['creation_date'] );
	        $mysqldate = date( 'Y-m-d H:i:s', $phpdate );

		$reservation = $wpdb->insert(DRECModel::get_escapia_reservations_table_name(),
			[

				'customer_id' => $customerId,
				'escapia_id' => (string)$reservation['escapia_id'],
				'reservation_type' => 'Reservation',
				'total_amount' =>  (string)$reservation['TotalAmount'],
				'remaining_amount' =>  (string)$reservation['remaining_amount'],
				'remaining_amount_deadline' => $amountRemainingDeadline,
				'reservation_id' =>  (string)$reservation['reservation_id'],
				'reservation_number' =>  (string)$reservation['reservation_number'],
				'confirmation_code' =>  (string)$reservation['confirmation_code'],
				'reservation_status' =>  (string)$reservation['reservation_status'],
				'arrival_date' => date('Y-m-d', strtotime($reservation['arrival_date'])),
				'departure_date' => date('Y-m-d', strtotime($reservation['departure_date'])),
				'reservation_date' => $mysqldate
			]);

        }

	}

	private function getOrSaveCustomer($input){
		global $wpdb;

		$customer = $wpdb->get_row('select * from '.DRECModel::get_escapia_customers_table_name().' where email="'. $input['Email'] .'"');

		if(isset($customer)){
			return $customer->id;
		}else{

			$new_customer = $wpdb->insert(DRECModel::get_escapia_customers_table_name(),
				['email' => $input['Email'],
				 'first_name' => $input['GivenName'],
				 'last_name' => $input['Surname'],
				 'phone' => $input['PhoneNumber']
				]
			);

			return $wpdb->insert_id;
		}
	}

}


function reservation_upload()
{
	$message        = '';
	$permittedTypes = array(
		'video/mp4',
		'video/webm',
		'video/ogg'
	);
	$destination    = __DIR__ . '/all_videos/';
	if ( ! empty( $_FILES['video_browse'] ) ) {
		if ( in_array( $_FILES['video_browse']['type'], $permittedTypes ) ) {
			if ( ! file_exists( $destination . $_FILES['video_browse']['name'] ) ) {
				move_uploaded_file( $_FILES['video_browse']['tmp_name'], $destination . $_FILES['video_browse']['name'] );
				$message = "upload was successfull.";
			} else {
				$message = $_FILES['video_browse']['name'] . ' already exists in the directory.';
			}
		}
	}
	die();
}

add_action( 'wp_ajax_reservation_uploading_ajax', 'reservation_upload' );


