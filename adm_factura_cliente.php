<?php include("inc_session.php"); ?>
<?php include("inc_seguridad.php"); ?>
<?php include("inc_conectarse.php"); ?>
<?php $_SESSION["modulo"] = "facturas"; ?>
<?php include("inc_functions.php"); ?>	
<?php 

	if ($_POST["action"]!="" || $_GET["action"]!="") {
	
		if ($_POST["action"]=="Generar") {
			$guia->envios = $_POST["chkEnvio"];
			$guia->id_chofer = $_POST["cmbChofer"];
			$action_result = generarGuiaTemporal($link, $guia);

			//$guia = obtenerGuiaChofer($link, $guia->id_chofer);
			$guia_tmp = "adm_guia_de_entrega.php?id=".$_SESSION["id_guia_tmp"];			
		}
	}
?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php include("inc_metadata.php"); ?>
<body>
<!-- wrap starts here -->
<?php include("inc_header.php"); ?>	
<div id="wrap"> 
	<?php include("inc_menu.php"); ?>
	<!-- content-wrap starts here -->	
	<div id="content-wrap">  
			<?php include("inc_sidebar.php"); ?>	
	  		<div id="main"> 
					
				<h1>Generar Factura Cliente</h1>						
					<?php include("inc_mensajes_crud.php"); ?> 				
					<?php if($guia_tmp!="") { ?>   
					<span style="padding-left:15px;">
					<strong>Haga <a href="<?php echo $guia_tmp; ?>">click aqui</a> para ver la Gu&iacute;a</strong>    
					</span>
					<?php } ?>
					<?php include("inc_lists.php"); ?>  
					<form name="buscar" action="adm_factura_cliente.php" method="post" 
					 enctype="multipart/form-data" 
                     style="background-color:#FFF; border:none;	margin:0px; padding-left:10px;"
                	 onSubmit="return validate_busqueda_cliente_form(buscar);">                   		  	
                     	<?php if($_POST["action"]=="Buscar") { ?>
                     	<input type="button" class="button" value="LIMPIAR BUSQUEDA" 
                     	 onclick="javascript:document.location.href = 'adm_factura_cliente.php';" />
                     	<?php } else { ?>
                     	<strong style="padding-left:5px">RIF</strong>&nbsp;
                     	<input type="text" name="txtRIF" size=15 style="border:1px solid #666"/>       		 	
	                    <input name="action" type="hidden" value="Buscar" />
	                    <input type="submit" class="button" value="BUSCAR" />
                     	<?php } ?>		 						
					</form> 
					<h3>Env&iacute;os Disponibles</h3> 
					<p><?php include("inc_mensajes.php"); ?></p>                 
					<form name="frmG" action="adm_generar_factura_cliente.php" method="post" enctype="multipart/form-data" 
                	 onSubmit="return validate_preparar_factura_form(this);"  
                     style="background-color:#FFF; border:none;	margin:0px; padding: 0px;">       
					<table style="padding-left:10px; font-size:11px" width="950px"> 
            			<tr>
                    		<td colspan="11"><hr></td>
                		</tr>            	
                		<tr>
                			<td width="40"><strong></strong></td>
                			<td width="220"><strong>PROVEEDOR</strong></td>
                			<td width="40" align="center"><strong>BULTOS</strong></td>
                			<td width="60" align="center"><strong>REMESA</strong></td>
                			<td width="70" align="center"><strong>FACTURA</strong></td>
                			<td width="350"><strong>CLIENTE</strong></td>
                			<td width="110"><strong>DESTINO</strong></td>
                    		<td width="70" align="right"><strong>MERCANCIA</strong></td>
                    		<td width="50" align="center"><strong>%</strong></td>
                            <td width="50" align="right"><strong>FLETE</strong></td>
                		</tr>
            			<tr>
                    		<td class="text" colspan="11"><hr></td>
                		</tr>
                		<?php
						
						$query = "SELECT e.* 
						            FROM ts_envio e,
						                 ts_proveedor p, ts_cliente c,
						                 ts_destino d 
						           WHERE e.ind_activo < 2
						             AND e.id_proveedor = p.id 
						             AND e.id_cliente = c.id 
						             AND e.id_destino = d.id 
						             AND e.ind_envio < 4 
						             AND e.id_factura IS NULL 
						             AND e.tipo_envio = 'F'"; 

						if($_POST["txtRIF"]!="") {
							$query .= " AND c.rif=".$_POST["txtRIF"];
						}		

						$query .= " ORDER BY e.fecha_creacion DESC, e.remesa, e.ind_envio";	
						
						$result = obtenerResultset($link,$query);
						$remesa = "";
						$colorear = false;
						while ($row = obtenerRegistro($result)) {
						
							$inactiva = false;
							if($row->ind_activo==0) {
								$inactiva = true;	
							}
							
							if($row->remesa!=$remesa) {
								if($colorear) { 
									$colorear = false;
								} 
								else {
									$colorear = true;
								}
							}
							$remesa = $row->remesa;
 						?>               
          				<tr style="<?php if($colorear) { ?> color:#C90; <?php } else { ?> color:#000; <?php } ?>">
                    		<?php 
	                			if($row->tipo_cobro=="V") { 
	          						$valor = $row->mercancia*($row->flete/100);
	 							}
	 							if($row->tipo_cobro=="P") {
	 								$valor = $row->peso*$row->bskg;
	 							}
	 							if($row->tipo_cobro=="M") {
	 								$valor = $row->viaje;
	 							}
	                    	?>
                			<td align="center">
                				<input type="checkbox" name="chkEnvio[]" value="<?php echo $row->id; ?>" 
                                 style="vertical-align:text-bottom" onClick="sumarFacturasCliente(frmG)" />
                			</td>
                			<td>
								<?php echo substr(obtenerProveedorStr($link, $row->id_proveedor),0,25); ?>
                    		</td>
                			<td <?php if($inactiva) {?> style="color:#999;" <?php } ?> align="center">
								<?php echo $row->bultos; ?>
                    		</td> 
                			<td <?php if($inactiva) {?> style="color:#999;" <?php } ?> align="center"> 
								<?php echo str_pad($row->remesa, 6, "0", STR_PAD_LEFT); ?>
                    		</td>
                			<td <?php if($inactiva) {?> style="color:#999;" <?php } ?> align="center"> 
								<?php echo substr(str_pad($row->factura, 6, "0", STR_PAD_LEFT),0,6); ?>
                    		</td>
                			<td <?php if($inactiva) {?> style="color:#999;" <?php } ?>>
								<?php echo substr(obtenerClienteStr($link, $row->id_cliente),0,35); ?>
                    		</td> 
                			<td <?php if($inactiva) {?> style="color:#999;" <?php } ?>>
								<?php echo substr(obtenerDestinoStr($link, $row->id_destino),0,15); ?>
                    		</td>                   		
                    		<td align="right">
								<?php echo number_format($row->mercancia,2,",","."); ?>
                    		</td>
                    		<td align="center">
								<?php if($row->flete!="") { echo $row->flete; ?>%<?php } ?>
                    		</td>		                		
                    		<td align="right">
								<?php echo number_format($valor,2,",","."); ?>
                    		</td> 
            			</tr>		
						<?php } ?>
                        
                        <tr>
                        	<td colspan="6">&nbsp;</td>
                        </tr>
            			<tr>
                    		<td class="text" colspan="11"><hr></td>
                		</tr>                        
                        <tr>
                        	<td align="left">
                        		<strong>&nbsp;</strong>
                            </td>
                        	<td align="left">
                        		<strong>BULTOS</strong>
                            </td>
                        	<td align="center">
                        		<strong><label id="bultos"></label></strong>
                            </td>
                        	<td align="center">
                        		<strong>FACTURAS</strong>
                            </td>
                        	<td align="center">
                        		<strong><label id="facturas"></label></strong>
                            </td>
                        	<td align="left">
                        		<strong>&nbsp;</strong>
                            </td>
                        	<td align="left">
                        		<strong>TOTAL Bs.</strong>
                            </td>
                        	<td align="right">
                        		<strong><label id="mercancia"></label></strong>
                            </td>
                        	<td align="right">
                        		<strong>&nbsp;</strong>
                            </td>
                        	<td align="right">
                        		<strong><label id="flete"></label></strong>
                            </td>
                        </tr>
            			<tr>
                    		<td class="text" colspan="11"><hr></td>
                		</tr> 
                        <tr>
                        	<td colspan="6">&nbsp;</td>
                        </tr> 
                        <tr>
                        	<td colspan="6"></td>
                        </tr>                                                                                               
          			</table>
          			 &nbsp;&nbsp;&nbsp;&nbsp;                		 	
                     <input name="action" type="hidden" value="Generar" />
                     <input type="submit" class="button" value="GENERAR FACTURA" />    
                     <input type="reset" class="button" value="CANCELAR" onClick="limpiarTotalesGuia()" />       			   
          			 <br /><br /><br /><br />
          			</form>           
                								
	  		</div> 	
	<!-- content-wrap ends here -->	
	</div>
<!-- wrap ends here -->
</div>		
<?php include("inc_footer.php"); ?>
</body>
</html>
<?php include("inc_desconectarse.php"); ?>