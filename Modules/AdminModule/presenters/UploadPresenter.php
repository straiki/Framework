<?php

namespace AdminModule;

use Nette\Http\FileUpload;


class UploadPresenter extends BasePresenter
{

	/**
	 * Uplaod file for ckeditor
	 * @return void
	 */
	public function actionDefault()
	{
		$file = new FileUpload($_FILES['upload']);

		$rawFilePath = '/images/uploads/' . time() . '_' . $file->getSanitizedName();
		$filePath =	$this->paramService->wwwDir . '/' . $rawFilePath;

		if ( ! $file->isOk()) {
			$message = 'No file uploaded.';

		} elseif ( ! $file->isImage()) {
			$message = 'The image must be in either JPG or PNG format. Please upload a JPG or PNG instead.';

		} else {
			$message = '';
			$move = $file->move($filePath);

			if ( ! $move) {
				$message = 'Error moving uploaded file. Check the script is granted Read/Write/Modify permissions.';
			}

			$basePath = $this->getHttpRequest()->url->baseUrl;
			$filePath = $basePath . $rawFilePath;
		}

		$funcNum = $_GET['CKEditorFuncNum'] ;
		echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$filePath', '$message');</script>";

		$this->terminate();
	}

}
