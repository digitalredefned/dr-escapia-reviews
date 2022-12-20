<?php
/**
 * Created by PhpStorm.
 * User: jamesmcdermott
 * Date: 9/24/18
 * Time: 7:00 PM
 */
! defined( 'ABSPATH' ) && exit;

add_action(
	'init',
	array( DRAdminReviewsEmailEditor::get_instance(), 'plugin_setup' )
);
class DRAdminReviewsEmailEditor {
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
			__( 'Review Request EMail' ),
			__( 'Review Request EMail' ),
			'manage_options',
			'dr_review_request_email_template',
			array( $this, 'dr_get_review_request_email_page' )
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
			array( $this, 'dr_get_review_request_email_page' )
		);


        error_log('registering scrxipts');

		add_action( 'load-' . $hook, array( $this, 'register_scripts' ) );
	}


	public function dr_get_review_request_email_page() {


		global $wpdb;
		$result_message='';
		$active=false;
		if(isset($_POST['body_intro'])) {


		    if(isset($_POST['active'])){
		        $active=true;
            }

			$templateUpdate = $wpdb->update(DRECModel::get_review_email_template_table_name(),
				['email_subject' => $_POST['subject'],
				'notification_email' => $_POST['notification_email'],
				 'logo' => $_POST['logo'],
				 'body_intro' => $_POST['body_intro'],
				 'body_exit' => $_POST['body_exit'],
				 'active' => $active],
				['id' =>1]);

			$result_message='email_settings_saved';

		}


		$this->template = $wpdb->get_row( "SELECT id,notification_email, email_subject, logo, body_intro, body_exit,active FROM ".DRECModel::get_review_email_template_table_name()." WHERE id=1" );
		?>
		<link rel="stylesheet" id="dresc_admin-css" href="<?php echo content_url();?>/plugins/dr-escapia/css/jquery-ui-fresh.css" type="text/css" media="all">
<!--		<link rel="stylesheet" id="dresc_admin-css" href="--><?php //echo content_url();?><!--/plugins/dr-escapia/css/admin.min.css?ver=2.4.14" type="text/css" media="all">-->
<!--		<link rel="stylesheet" id="dresc_admin-css" href="--><?php //echo content_url();?><!--/plugins/dr-escapia/css/tooltip.css?ver=2.4.14" type="text/css" media="all">-->
<!--		<link rel="stylesheet" id="dresc_admin-css" href="--><?php //echo content_url();?><!--/plugins/dr-escapia/css/font-awesome.css?ver=2.4.14" type="text/css" media="all">-->
		<style>
            #dialog_link {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
            #dialog_link span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
            .ui-dialog {position: absolute; top: 0;left: 0; z-index: 100102 !important;background-color: #fff;box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);overflow: hidden; }
		</style>
		<div class="wrap">
			<form method="post" action="">
				<input type="hidden" name="email_template_id" value="<?php echo $this->template->id; ?>">
				<h1>Email Template Details</h1>
				<?php if (isset($_POST) && ($result_message =='email_settings_saved')) { ?>
					<div class="notice notice-success inline">
						<p>
							Template Settings Saved</p>
					</div>
				<?php } ?>
				<div id="poststuff">

					<div id="post-body" class="metabox-holder columns-2">
						<div class="inside">


							<table class="form-table">
								<tbody>

								<tr valign="top">
									<th scope="row">
										<label for="subject">Active(No emails will be sent if inactive</label>
									</th>
									<td>
										<p><input type="checkbox" name="active" value="active" <?php if ($this->template->active==1) { echo 'checked';  } ?>></p>
									</td>
								</tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="notification_emai">Notification Email
                                    </th>
                                    <td>
                                        <input type="text" name="notification_email" id="notification_email" value="<?php echo $this->template->notification_email ?>"/>

                                    </td>
                                </tr>
								<tr valign="top">
									<th scope="row">
										<label for="subject">Subject</label>
									</th>
									<td>
										<p><input type="text" id="subject" name="subject" value="<?php echo $this->template->email_subject ?>" class="regular-text"></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="logo">Logo</label> <?php dresc_tooltip( 'email_template_logo' ) ?>
									</th>
									<td>
										<p><input type="text" id="logo" name="logo" value="<?php echo $this->template->logo ?>" placeholder="https://www.site.com/logo.jpg" class="regular-text" style="width: 80%;"></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="body_intro">Body  Message</label><?php dresc_tooltip( 'email_template_intro' ) ?>
									</th>
									<td>
										<textarea type="text" id="body_intro" name="body_intro"  class="regular-text" rows="15" cols="150" style="width: 80%;"><?php echo $this->template->body_intro ?></textarea></p>
									</td>
								</tr>
								<tr valign="top">
                                    <th scope="row">
                                        <label for="body_exit">Signature Message</label><?php dresc_tooltip( 'email_template_exit' ) ?>
                                    </th>
                                    <td>
                                        <p><textarea type="text" id="body_exit" name="body_exit"  class="regular-text" rows="15" cols="50" style="width: 80%;"><?php echo $this->template->body_exit ?></textarea></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><input class="button-primary" type="submit" name="save" value="Save Email">
                                        <a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span
                                                    class="ui-icon ui-icon-newwin"></span>View Email</a>
                                    </td>
                                </tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</form>
		</div>

		<div id="dialog" title="Email Sample">
           <span style=" display: block;margin-left: auto;margin-right: auto;width: 50%;"> <img  class="body_image_sample" src="" alt=""></span>
			<p class="body_intro_sample"></p>
            <a href="#">Submit Your Review</a>
			<p class="body_exit_sample"></p>
		</div>

<!--		<script src='--><?php //echo content_url();?><!--/plugins/dr-escapia/js/tooltip_init.js' type='text/javascript'></script>-->

      <?php
		add_action('init', 'review_email_editor_script');
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		?>
        <script>
            jQuery(document).ready(function($) {
// Dialog
                jQuery('#dialog').dialog({
                    autoOpen: false,
                    width: 600,
                    modal: true,
                    buttons: {
                        "Close": function () {
                            $(this).dialog("close");
                        },

                    },
                    open: function (event, ui) {
                        $('.ui-widget-overlay').bind('click', function () {
                            $("#dialog").dialog('close');
                        });
                    }
                });

// Dialog Link
                $('#dialog_link').click(function () {

                    // var html =  '<div class="col-lg-4 col-references" idreference="'+response+'"><span class="optionsRefer"><i class="glyphicon glyphicon-remove delRefer" style="color:red; cursor:pointer;" data-toggle="modal" data-target="#modalDel"></i><i class="glyphicon glyphicon-pencil editRefer" data-toggle="modal" data-target="#modalRefer" style="cursor:pointer;"></i></span><div id="contentRefer'+response+'">'+refer_summary+'</div><span id="nameRefer'+response+'">'+refer_name+'</span></div>';
                    $introHTML = $.parseHTML( $('#body_intro').val());
                    $mailImage = jQuery('#logo').val();
                    $amountRemainingHTML = $.parseHTML( $('#amount_remaining_message').val());
                    $exitHTML = $.parseHTML( $('#body_exit').val());

                    console.log($mailImage);
                    $('.body_intro_sample').empty();
                    $('.body_image_sample').attr("src", "");
                    $('.amount_remaining_message_sample').empty();
                    $('.body_exit_sample').empty();
                    $('.body_intro_sample').append($introHTML);
                    $('.amount_remaining_message_sample').append($amountRemainingHTML);
                    $('.body_image_sample').attr("src", $mailImage);
                    $('.body_exit_sample').append($exitHTML);


                    $('#dialog').dialog('open');
                    // Make the overlay fill the whole screen
                    $('.ui-widget-overlay').width($(document).width());
                    $('.ui-widget-overlay').height($(document).height());
                    $('.ui-dialog').css('z-index', '9999');
                    return false;
                });
            });
        </script>
	<?php }




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


