<?php
namespace REDCapCon\REDCapConTestModule;

class REDCapConTestModule extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	public function redcap_add_edit_records_page( int $project_id, string $instrument, int $event_id ) {
		
	}

	
	public function redcap_control_center(  ) {
		
	}

	
	public function redcap_custom_verify_username( string $username ) {
		
	}

	
	public function redcap_data_entry_form( int $project_id, string $record, string $instrument, int $event_id, int $group_id, int $repeat_instance ) {
		
	}

	
	public function redcap_data_entry_form_top( int $project_id, string $record, string $instrument, int $event_id, int $group_id, int $repeat_instance ) {
		
	}

	
	public function redcap_email( string $to, string $from, string $subject, string $message, string $cc, string $bcc, string $fromName, array $attachments ) {
		
	}

	
	public function redcap_every_page_before_render( int $project_id ) {
		
	}

	
	public function redcap_every_page_top( int $project_id ) {
		
	}

	
	public function redcap_pdf( int $project_id, array $metadata, array $data, string $instrument, string $record, int $event_id, int $instance ) {
		
	}

	
	public function redcap_project_home_page( int $project_id ) {
		
	}

	
	public function redcap_save_record( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_acknowledgement_page( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_complete( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_page( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_page_top( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_user_rights(  int $project_id ) {
		
	}

	
	public function redcap_module_system_enable( $version ) {
		
	}

	
	public function redcap_module_system_disable( $version ) {
		
	}

	
	public function redcap_module_system_change_version( $version, $old_version ) {
		
	}

	
	public function redcap_module_project_enable( $version, $project_id ) {
		
	}

	
	public function redcap_module_project_disable( $version, $project_id ) {
		
	}

	
	public function redcap_module_link_check_display( $project_id, $link ) {
		
	}

	
	public function redcap_module_save_configuration( $project_id ) {
		
	}

	

	public function run_cron( $cronParameters ) {

	}
}