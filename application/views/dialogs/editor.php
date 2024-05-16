<div id="editor">
	<?=$html;?>
</div>

<script src="/jscript/ckeditor.js"></script>

<script>
	ClassicEditor
		.create( document.querySelector( '#editor' ), {
			//toolbar: [ 'heading', '|', 'bold', 'italic', 'link' ]
		} )
		.then(	editor	=> { window.editor = editor; } )
		.catch(		err	=> { console.error( err.stack ); } );
</script>