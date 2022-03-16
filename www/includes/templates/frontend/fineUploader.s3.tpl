<script type="text/template" id="qq-template-s3">
	<div class="qq-uploader-selector qq-uploader" qq-drop-area-text="{t}Drop files here{/t}">
<!--		<div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container">
			<div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
		</div> -->
		<div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
			<span class="qq-upload-drop-area-text-selector"></span>
		</div>
		<div class="qq-upload-button-selector qq-upload-button">
			<div>{t}Upload a file{/t}</div>
		</div>
		<span class="qq-drop-processing-selector qq-drop-processing">
			<span>{t}Processing dropped files...{/t}</span>
			<span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
		</span>
		<ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
			<li>
				<div class="qq-progress-bar-container-selector">
					<div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
				</div>
				<span class="qq-upload-spinner-selector qq-upload-spinner"></span>
				<img class="qq-thumbnail-selector" qq-max-size="100" qq-server-scale>
				<span class="qq-upload-file-selector qq-upload-file"></span>
				<span class="qq-edit-filename-icon-selector qq-edit-filename-icon" aria-label="{t}Edit filename{/t}"></span>
				<input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
				<span class="qq-upload-size-selector qq-upload-size"></span>
				<button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel">{t}Cancel{/t}</button>
				<button type="button" class="qq-btn qq-upload-retry-selector qq-upload-retry">{t}Retry{/t}</button>
				<button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete">{t}Delete{/t}</button>
				<span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
			</li>
		</ul>

		<dialog class="qq-alert-dialog-selector">
			<div class="qq-dialog-message-selector"></div>
			<div class="qq-dialog-buttons">
				<button type="button" class="qq-cancel-button-selector">{t}Close{/t}</button>
			</div>
		</dialog>

		<dialog class="qq-confirm-dialog-selector">
			<div class="qq-dialog-message-selector"></div>
			<div class="qq-dialog-buttons">
				<button type="button" class="qq-cancel-button-selector">{t}No{/t}</button>
				<button type="button" class="qq-ok-button-selector">{t}Yes{/t}</button>
			</div>
		</dialog>

		<dialog class="qq-prompt-dialog-selector">
			<div class="qq-dialog-message-selector"></div>
			<input type="text">
			<div class="qq-dialog-buttons">
				<button type="button" class="qq-cancel-button-selector">{t}Cancel{/t}</button>
				<button type="button" class="qq-ok-button-selector">{t}Ok{/t}</button>
			</div>
		</dialog>
	</div>
</script>
