<tr class="trlist">
	<td class="cflist col1">
		<input type="checkbox" id="ffc_file_<?=$id;?>">
	</td>
	<td class="cflist t_filename col2">
		<div id="fn_<?=$id;?>">
			<img class="ico" src="/images/ico/ext/<?=$fileType;?>.png">&nbsp;
			<a class="fhref" target="_blank" title="<?=$comments;?>" href="/storage/download/<?=$userID;?>/<?=$storageFilename;?>">
				<span class="<?=$fileStyle;?>"><?=$originalFilename;?></span>
			</a>
		</div>
	</td>
	<td class="cflist col3"><?=$fileSize;?></td>
	<td class="cflist col4"><?=$uploadDate;?></td>
	<td class="cflist col5"><?=$deletionDate;?></td>
</tr>