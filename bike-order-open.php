<?php require_once("includes/initialize.php");

if(!$session->is_logged_in()) {
	redirect_to("login.php");
}

$id_order = null;
$action = null;
if (isset($_POST['action'])) {
	$action = $_POST['action'];
}

if ($action=='close') {
	
	$id = $_POST['id_order'];
	$order = Orders::find_by_what('id', $id);
	if ($order) { 
		$order->status = 'Cerrado';
	}
	
	if ($order->update_one_by_what($id, 'status', $order->status)) {
			// Success
			$session->message("The order has been closed successfully");
			$today_timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
			$order->update_one_by_what($id, 'finished_time', $today_timestamp);
			redirect_to('bike-order-open.php');
		} else {
			// Failure
			$message = "The order has not been closed"; //join("<br />", $item->errors);
			redirect_to('bike-order-open.php');
		}

} else {
	//$bike_array = User::find_all();
	$orders_array = Orders::find_all_by_what('status','"Abierto"');
	$id_bike = $_SESSION['user_id'];
	$bike = User::find_by_what('id', $id_bike);
	foreach($orders_array as $key => $value) {
		if ($value->id_bike != $id_bike) {
			unset($orders_array[$key]);
		}
	}
}
	
include_layout_template('header.php');

?>

<?php include_layout_template('bike-navigation.php'); ?>

<div class="container">

	<?php if($message) { echo '<div class="alert alert-info">'.output_message($message).'</div>';} ?>
	<h3>Pedidos abiertos:</h3>
	<?php
		if($orders_array) {
			echo '<div class="table-responsive">';
			$table_header  = '<table class="table table-hover"><tr>';
			$table_header .= '<th>Rest</th>';
			$table_header .= '<th>Address</th>';
			$table_header .= '<th>For</th>';
			//$table_header .= '<th>Motorizado</th>';
			$table_header .= '<th>Actions</th>';
			$table_header .= '</tr>';
			echo $table_header;
			$row_count = 0;
			foreach($orders_array as $key => $value) {
				$row_count = $row_count + 1;
				$restaurant = Restaurant::find_by_what('id', $value->id_rest);
				$bike = User::find_by_what('id', $value->id_bike);
				$table_body  = '<tr>';
				$table_body .= '<td>'.$restaurant->name.'</td>';
				$table_body .= '<td>'.$value->street_name.' '.$value->street_number.'<br>cp: '.$value->postal_code.'</td>';
				$table_body .= '<td>'.date('H:i', strtotime($value->accepted_to_time)).'</td>';
				// $table_body .= '<td>';
				// if ($bike) { 
				// 	$table_body .= $bike->full_name();
				// } else {
				// 	$table_body .= 'Sin asignar';
				// }
				// $table_body .= '</td>';
				$table_body .= '<td>';
				$table_body .= '<table><tr>';
				$table_body .= '<td><form action="bike-order-open.php" method="POST">';
				$table_body .= '<input type="hidden" name="action" value="close">';
				$table_body .= '<input type="hidden" name="id_order" value="'.$value->id.'">';
				$table_body .= '<input type="submit" value="Cerrar" class="btn btn-primary btn-xs" />';
				$table_body .= '</form></td>';
				$table_body .= '<td><form action="bike-order-details.php" method="GET">';
				$table_body .= '<input type="hidden" name="id_order" value="'.$value->id.'">';
				$table_body .= '<input type="submit" value="Detalles" class="btn btn-info btn-xs" />';
				$table_body .= '</form></td>';
				// $table_body .= '<td><form action="order-edit.php" method="POST">';
				// $table_body .= '<input type="hidden" name="action" value="delete">';
				// $table_body .= '<input type="hidden" name="id_order" value="'.$value->id.'">';
				// $table_body .= '<input type="submit" value="Borrar" class="btn btn-danger btn-xs" />';
				// $table_body .= '</form></td>';
				$table_body .= '</tr></table>';
				$table_body .= '</td>';
				$table_body .= '</tr>';
				echo $table_body;
			}
			echo '</table></div>';
		} else {
			echo "<p>The list is empty.</p>";
		}
	?>
</div>

<?php include_layout_template('footer.php'); ?>