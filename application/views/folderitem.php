<tr class="trlist">
	<td class="cflist col1">
		<input type="checkbox" id="ffc_folder_<?=$id;?>">
	</td>
	<td class="cflist t_filename col2">
		<div id="ff_<?=$id;?>">
			<img class="ico" src="/images/ico/folder.png">&nbsp;
			<a class="fhref" href="#" title="<?=$comments;?>" onclick="change_folder(<?=$id;?>);">
				<span class="foldernames"><?=$folderName;?></span>
			</a>
		</div>
	</td>
	<td class="cflist col3">
		<acronym title="Папок: <?=$foldersCount;?> / Файлов: <?=$filesCount;?>">&lsaquo; Папка &rsaquo;</acronym>
	</td>
	<td class="cflist col4"><?=date("d.m.Y H:i", $creationDate);?></td>
	<td class="cflist col5"><?=date("d.m.Y", $deletionDate);?></td>
</tr>