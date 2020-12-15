<?php


class DRReviewEmailProcessor {

	public static function processEmailNotifications(){

		global $wpdb;
		$emailContent = $wpdb->get_row('SELECT * FROM '.DRECModel::get_review_email_template_table_name());
		$reservationsForProcessing = $wpdb->get_results('SELECT reservations.id, reservation_number, arrival_date, departure_date, first_name, last_name, email FROM '.DRECModel::get_escapia_reservations_table_name(). ' as reservations
                                     INNER JOIN '.DRECModel::get_escapia_customers_table_name().' as customers ON customers.id=reservations.customer_id
                                     WHERE review_request_status is null
                                       AND reservation_status="Checked out"'
		);

		if($emailContent>active==1){
		
		foreach ($reservationsForProcessing as $reservation){
			error_log(print_r($reservation,true));

			$message ='';

			$message .='
           <span style=" display: block;"> <img class="body_image_sample" src="'.$emailContent->logo.'" alt=""></span>
			<p class="body_intro_sample">Dear '.$reservation->first_name.'</p>
			<p class="body_intro_sample">'.$emailContent->body_intro.'</p>
            <a href="http://escapia-wp.loc/review-submission/?reservation_number='.$reservation->reservation_number.'" style="background: #7dbddf;
    padding: 10px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    font-size: 20px;">Submit Your Review</a>
			<p class="body_exit_sample">'.$emailContent->body_exit.'</p>
		';
			$headers = ['Content-Type: text/html; charset=ISO-8859-1', 'From: jim@digitalredefined.com'];
			error_log($message);
			wp_mail($reservation->email, $emailContent->email_subject, $message, $headers);



			DRReviewEmailProcessor::updateReviewRequestStatus($reservation->id);
		}
		}
	}

	public static  function updateReviewRequestStatus($reservationId){
		global $wpdb;

		$wpdb->update(
			DRECModel::get_escapia_reservations_table_name(),
			['review_request_status' => 'sent'],
			['id'=>$reservationId]
		);
	}


}