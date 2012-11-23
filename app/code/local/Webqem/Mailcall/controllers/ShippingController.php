<?php
class Webqem_Mailcall_ShippingController extends Mage_Core_Controller_Front_Action
{
	public function getTimeslotAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			$day = date('N', strtotime($data['day']));
			
			$timeslots = Mage::getModel('webqemmailcall/timeslot')->getAllTimeslotByDay($day);
			
			$html = "<select name='shipping_pickup[timeslot]' style='width:170px'>";
			foreach ($timeslots as $timeslot) {
				if (date("m/d/Y") == $data['day']) {
					$currentTime = floatval(date("H.i"));
					$timestart = floatval(str_replace(':', '.', $timeslot->getTimeStart()));
					
					if ($timestart>$currentTime) {
						$html .="<option value='".$timeslot->getId()."'>".$timeslot->getDescription()." " . $timeslot->getTimeStart() ." to " . $timeslot->getTimeEnd(). "</option>";
					}
					
				} else {
					$html .="<option value='".$timeslot->getId()."'>".$timeslot->getDescription()." " . $timeslot->getTimeStart() ." to " . $timeslot->getTimeEnd(). "</option>";
				}
				
			}
			echo $html."</select>";
			die;
		}
	}
}