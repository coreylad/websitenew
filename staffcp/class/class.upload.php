<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

class Class_3
{
    public $version = NULL;
    public $file_src_name = NULL;
    public $file_src_name_body = NULL;
    public $file_src_name_ext = NULL;
    public $file_src_mime = NULL;
    public $file_src_size = NULL;
    public $file_src_error = NULL;
    public $file_src_pathname = NULL;
    public $file_src_temp = NULL;
    public $file_dst_path = NULL;
    public $file_dst_name = NULL;
    public $file_dst_name_body = NULL;
    public $file_dst_name_ext = NULL;
    public $file_dst_pathname = NULL;
    public $image_src_x = NULL;
    public $image_src_y = NULL;
    public $image_src_bits = NULL;
    public $image_src_pixels = NULL;
    public $image_src_type = NULL;
    public $image_dst_x = NULL;
    public $image_dst_y = NULL;
    public $image_supported = NULL;
    public $file_is_image = NULL;
    public $uploaded = NULL;
    public $no_upload_check = NULL;
    public $processed = NULL;
    public $error = NULL;
    public $log = NULL;
    public $file_new_name_body = NULL;
    public $file_name_body_add = NULL;
    public $file_name_body_pre = NULL;
    public $file_new_name_ext = NULL;
    public $file_safe_name = NULL;
    public $mime_check = NULL;
    public $mime_fileinfo = NULL;
    public $mime_file = NULL;
    public $mime_magic = NULL;
    public $mime_getimagesize = NULL;
    public $no_script = NULL;
    public $file_auto_rename = NULL;
    public $dir_auto_create = NULL;
    public $dir_auto_chmod = NULL;
    public $dir_chmod = NULL;
    public $file_overwrite = NULL;
    public $file_max_size = NULL;
    public $image_resize = NULL;
    public $image_convert = NULL;
    public $image_x = NULL;
    public $image_y = NULL;
    public $image_ratio = NULL;
    public $image_ratio_crop = NULL;
    public $image_ratio_fill = NULL;
    public $image_ratio_pixels = NULL;
    public $image_ratio_no_zoom_in = NULL;
    public $image_ratio_no_zoom_out = NULL;
    public $image_ratio_x = NULL;
    public $image_ratio_y = NULL;
    public $image_max_width = NULL;
    public $image_max_height = NULL;
    public $image_max_pixels = NULL;
    public $image_max_ratio = NULL;
    public $image_min_width = NULL;
    public $image_min_height = NULL;
    public $image_min_pixels = NULL;
    public $image_min_ratio = NULL;
    public $jpeg_quality = NULL;
    public $jpeg_size = NULL;
    public $preserve_transparency = NULL;
    public $image_is_transparent = NULL;
    public $image_transparent_color = NULL;
    public $image_background_color = NULL;
    public $image_default_color = NULL;
    public $image_is_palette = NULL;
    public $image_brightness = NULL;
    public $image_contrast = NULL;
    public $image_threshold = NULL;
    public $image_tint_color = NULL;
    public $image_overlay_color = NULL;
    public $image_overlay_percent = NULL;
    public $image_negative = NULL;
    public $image_greyscale = NULL;
    public $image_text = NULL;
    public $image_text_direction = NULL;
    public $image_text_color = NULL;
    public $image_text_percent = NULL;
    public $image_text_background = NULL;
    public $image_text_background_percent = NULL;
    public $image_text_font = NULL;
    public $image_text_position = NULL;
    public $image_text_x = NULL;
    public $image_text_y = NULL;
    public $image_text_padding = NULL;
    public $image_text_padding_x = NULL;
    public $image_text_padding_y = NULL;
    public $image_text_alignment = NULL;
    public $image_text_line_spacing = NULL;
    public $image_reflection_height = NULL;
    public $image_reflection_space = NULL;
    public $image_reflection_color = NULL;
    public $image_reflection_opacity = NULL;
    public $image_flip = NULL;
    public $image_rotate = NULL;
    public $image_crop = NULL;
    public $image_precrop = NULL;
    public $image_bevel = NULL;
    public $image_bevel_color1 = NULL;
    public $image_bevel_color2 = NULL;
    public $image_border = NULL;
    public $image_border_color = NULL;
    public $image_frame = NULL;
    public $image_frame_colors = NULL;
    public $image_watermark = NULL;
    public $image_watermark_position = NULL;
    public $image_watermark_x = NULL;
    public $image_watermark_y = NULL;
    public $allowed = NULL;
    public $forbidden = NULL;
    public $translation = NULL;
    public $language = NULL;
    public function function_38()
    {
        $this->$file_new_name_body = "";
        $this->$file_name_body_add = "";
        $this->$file_name_body_pre = "";
        $this->$file_new_name_ext = "";
        $this->$file_safe_name = true;
        $this->$file_overwrite = false;
        $this->$file_auto_rename = true;
        $this->$dir_auto_create = true;
        $this->$dir_auto_chmod = true;
        $this->$dir_chmod = 511;
        $this->$mime_check = true;
        $this->$mime_fileinfo = true;
        $this->$mime_file = true;
        $this->$mime_magic = true;
        $this->$mime_getimagesize = true;
        $this->$no_script = true;
        $val = trim(ini_get("upload_max_filesize"));
        $var_89 = strtolower($val[strlen($val) - 1]);
        switch ($var_89) {
            case "g":
                $val *= 1024;
                break;
            case "m":
                $val *= 1024;
                break;
            case "k":
                $val *= 1024;
                break;
            default:
                $this->$file_max_size = $val;
                $this->$image_resize = false;
                $this->$image_convert = "";
                $this->$image_x = 150;
                $this->$image_y = 150;
                $this->$image_ratio = false;
                $this->$image_ratio_crop = false;
                $this->$image_ratio_fill = false;
                $this->$image_ratio_pixels = false;
                $this->$image_ratio_no_zoom_in = false;
                $this->$image_ratio_no_zoom_out = false;
                $this->$image_ratio_x = false;
                $this->$image_ratio_y = false;
                $this->$jpeg_quality = 85;
                $this->$jpeg_size = NULL;
                $this->$preserve_transparency = false;
                $this->$image_is_transparent = false;
                $this->$image_transparent_color = NULL;
                $this->$image_background_color = NULL;
                $this->$image_default_color = "#ffffff";
                $this->$image_is_palette = false;
                $this->$image_max_width = NULL;
                $this->$image_max_height = NULL;
                $this->$image_max_pixels = NULL;
                $this->$image_max_ratio = NULL;
                $this->$image_min_width = NULL;
                $this->$image_min_height = NULL;
                $this->$image_min_pixels = NULL;
                $this->$image_min_ratio = NULL;
                $this->$image_brightness = NULL;
                $this->$image_contrast = NULL;
                $this->$image_threshold = NULL;
                $this->$image_tint_color = NULL;
                $this->$image_overlay_color = NULL;
                $this->$image_overlay_percent = NULL;
                $this->$image_negative = false;
                $this->$image_greyscale = false;
                $this->$image_text = NULL;
                $this->$image_text_direction = NULL;
                $this->$image_text_color = "#FFFFFF";
                $this->$image_text_percent = 100;
                $this->$image_text_background = NULL;
                $this->$image_text_background_percent = 100;
                $this->$image_text_font = 5;
                $this->$image_text_x = NULL;
                $this->$image_text_y = NULL;
                $this->$image_text_position = NULL;
                $this->$image_text_padding = 0;
                $this->$image_text_padding_x = NULL;
                $this->$image_text_padding_y = NULL;
                $this->$image_text_alignment = "C";
                $this->$image_text_line_spacing = 0;
                $this->$image_reflection_height = NULL;
                $this->$image_reflection_space = 2;
                $this->$image_reflection_color = "#ffffff";
                $this->$image_reflection_opacity = 60;
                $this->$image_watermark = NULL;
                $this->$image_watermark_x = NULL;
                $this->$image_watermark_y = NULL;
                $this->$image_watermark_position = NULL;
                $this->$image_flip = NULL;
                $this->$image_rotate = NULL;
                $this->$image_crop = NULL;
                $this->$image_precrop = NULL;
                $this->$image_bevel = NULL;
                $this->$image_bevel_color1 = "#FFFFFF";
                $this->$image_bevel_color2 = "#000000";
                $this->$image_border = NULL;
                $this->$image_border_color = "#FFFFFF";
                $this->$image_frame = NULL;
                $this->$image_frame_colors = "#FFFFFF #999999 #666666 #000000";
                $this->$forbidden = [];
                $this->$allowed = ["application/arj", "application/excel", "application/gnutar", "application/mspowerpoint", "application/msword", "application/octet-stream", "application/onenote", "application/pdf", "application/plain", "application/postscript", "application/powerpoint", "application/rar", "application/rtf", "application/vnd.ms-excel", "application/vnd.ms-excel.addin.macroEnabled.12", "application/vnd.ms-excel.sheet.binary.macroEnabled.12", "application/vnd.ms-excel.sheet.macroEnabled.12", "application/vnd.ms-excel.template.macroEnabled.12", "application/vnd.ms-office", "application/vnd.ms-officetheme", "application/vnd.ms-powerpoint", "application/vnd.ms-powerpoint.addin.macroEnabled.12", "application/vnd.ms-powerpoint.presentation.macroEnabled.12", "application/vnd.ms-powerpoint.slide.macroEnabled.12", "application/vnd.ms-powerpoint.slideshow.macroEnabled.12", "application/vnd.ms-powerpoint.template.macroEnabled.12", "application/vnd.ms-word", "application/vnd.ms-word.document.macroEnabled.12", "application/vnd.ms-word.template.macroEnabled.12", "application/vnd.oasis.opendocument.chart", "application/vnd.oasis.opendocument.database", "application/vnd.oasis.opendocument.formula", "application/vnd.oasis.opendocument.graphics", "application/vnd.oasis.opendocument.graphics-template", "application/vnd.oasis.opendocument.image", "application/vnd.oasis.opendocument.presentation", "application/vnd.oasis.opendocument.presentation-template", "application/vnd.oasis.opendocument.spreadsheet", "application/vnd.oasis.opendocument.spreadsheet-template", "application/vnd.oasis.opendocument.text", "application/vnd.oasis.opendocument.text-master", "application/vnd.oasis.opendocument.text-template", "application/vnd.oasis.opendocument.text-web", "application/vnd.openofficeorg.extension", "application/vnd.openxmlformats-officedocument.presentationml.presentation", "application/vnd.openxmlformats-officedocument.presentationml.slide", "application/vnd.openxmlformats-officedocument.presentationml.slideshow", "application/vnd.openxmlformats-officedocument.presentationml.template", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.spreadsheetml.template", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.openxmlformats-officedocument.wordprocessingml.template", "application/vocaltec-media-file", "application/wordperfect", "application/x-bittorrent", "application/x-bzip", "application/x-bzip2", "application/x-compressed", "application/x-excel", "application/x-gzip", "application/x-latex", "application/x-midi", "application/xml", "application/x-msexcel", "application/x-rar-compressed", "application/x-rtf", "application/x-shockwave-flash", "application/x-sit", "application/x-stuffit", "application/x-troff-msvideo", "application/x-zip", "application/x-zip-compressed", "application/zip", "audio/*", "image/*", "multipart/x-gzip", "multipart/x-zip", "text/plain", "text/richtext", "text/xml", "video/*"];
        }
    }
    public function __construct($file, $lang = "en_GB")
    {
        $this->$version = "0.29";
        $this->$file_src_name = "";
        $this->$file_src_name_body = "";
        $this->$file_src_name_ext = "";
        $this->$file_src_mime = "";
        $this->$file_src_size = "";
        $this->$file_src_error = "";
        $this->$file_src_pathname = "";
        $this->$file_src_temp = "";
        $this->$file_dst_path = "";
        $this->$file_dst_name = "";
        $this->$file_dst_name_body = "";
        $this->$file_dst_name_ext = "";
        $this->$file_dst_pathname = "";
        $this->$image_src_x = NULL;
        $this->$image_src_y = NULL;
        $this->$image_src_bits = NULL;
        $this->$image_src_type = NULL;
        $this->$image_src_pixels = NULL;
        $this->$image_dst_x = 0;
        $this->$image_dst_y = 0;
        $this->$uploaded = true;
        $this->$no_upload_check = false;
        $this->$processed = true;
        $this->$error = "";
        $this->$log = "";
        $this->$allowed = [];
        $this->$forbidden = [];
        $this->$file_is_image = false;
        $this->function_38();
        $info = NULL;
        $mime_from_browser = NULL;
        $this->$translation = [];
        $this->translation["file_error"] = "File error. Please try again.";
        $this->translation["local_file_missing"] = "Local file doesn't exist.";
        $this->translation["local_file_not_readable"] = "Local file is not readable.";
        $this->translation["uploaded_too_big_ini"] = "File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini).";
        $this->translation["uploaded_too_big_html"] = "File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form).";
        $this->translation["uploaded_partial"] = "File upload error (the uploaded file was only partially uploaded).";
        $this->translation["uploaded_missing"] = "File upload error (no file was uploaded).";
        $this->translation["uploaded_no_tmp_dir"] = "File upload error (missing a temporary folder).";
        $this->translation["uploaded_cant_write"] = "File upload error (failed to write file to disk).";
        $this->translation["uploaded_err_extension"] = "File upload error (file upload stopped by extension).";
        $this->translation["uploaded_unknown"] = "File upload error (unknown error code).";
        $this->translation["try_again"] = "File upload error. Please try again.";
        $this->translation["file_too_big"] = "File too big.";
        $this->translation["no_mime"] = "MIME type can't be detected.";
        $this->translation["incorrect_file"] = "Incorrect type of file.";
        $this->translation["image_too_wide"] = "Image too wide.";
        $this->translation["image_too_narrow"] = "Image too narrow.";
        $this->translation["image_too_high"] = "Image too high.";
        $this->translation["image_too_short"] = "Image too short.";
        $this->translation["ratio_too_high"] = "Image ratio too high (image too wide).";
        $this->translation["ratio_too_low"] = "Image ratio too low (image too high).";
        $this->translation["too_many_pixels"] = "Image has too many pixels.";
        $this->translation["not_enough_pixels"] = "Image has not enough pixels.";
        $this->translation["file_not_uploaded"] = "File not uploaded. Can't carry on a process.";
        $this->translation["already_exists"] = "%s already exists. Please change the file name.";
        $this->translation["temp_file_missing"] = "No correct temp source file. Can't carry on a process.";
        $this->translation["source_missing"] = "No correct uploaded source file. Can't carry on a process.";
        $this->translation["destination_dir"] = "Destination directory can't be created. Can't carry on a process.";
        $this->translation["destination_dir_missing"] = "Destination directory doesn't exist. Can't carry on a process.";
        $this->translation["destination_path_not_dir"] = "Destination path is not a directory. Can't carry on a process.";
        $this->translation["destination_dir_write"] = "Destination directory can't be made writeable. Can't carry on a process.";
        $this->translation["destination_path_write"] = "Destination path is not a writeable. Can't carry on a process.";
        $this->translation["temp_file"] = "Can't create the temporary file. Can't carry on a process.";
        $this->translation["source_not_readable"] = "Source file is not readable. Can't carry on a process.";
        $this->translation["no_create_support"] = "No create from %s support.";
        $this->translation["create_error"] = "Error in creating %s image from source.";
        $this->translation["source_invalid"] = "Can't read image source. Not an image?.";
        $this->translation["gd_missing"] = "GD doesn't seem to be present.";
        $this->translation["watermark_no_create_support"] = "No create from %s support, can't read watermark.";
        $this->translation["watermark_create_error"] = "No %s read support, can't create watermark.";
        $this->translation["watermark_invalid"] = "Unknown image format, can't read watermark.";
        $this->translation["file_create"] = "No %s create support.";
        $this->translation["no_conversion_type"] = "No conversion type defined.";
        $this->translation["copy_failed"] = "Error copying file on the server. copy() failed.";
        $this->translation["reading_failed"] = "Error reading the file.";
        $this->$lang = $lang;
        if ($this->lang != "en_GB" && file_exists(dirname(__FILE__) . "/lang") && file_exists(dirname(__FILE__) . "/lang/class.upload." . $lang . ".php")) {
            $translation = NULL;
            include dirname(__FILE__) . "/lang/class.upload." . $lang . ".php";
            if (is_array($translation)) {
                $this->$translation = array_merge($this->translation, $translation);
            } else {
                $this->$lang = "en_GB";
            }
        }
        $this->$image_supported = [];
        if ($this->function_39()) {
            if (imagetypes() & IMG_GIF) {
                $this->image_supported["image/gif"] = "gif";
            }
            if (imagetypes() & IMG_JPG) {
                $this->image_supported["image/jpg"] = "jpg";
                $this->image_supported["image/jpeg"] = "jpg";
                $this->image_supported["image/pjpeg"] = "jpg";
            }
            if (imagetypes() & IMG_PNG) {
                $this->image_supported["image/png"] = "png";
                $this->image_supported["image/x-png"] = "png";
            }
            if (imagetypes() & IMG_WBMP) {
                $this->image_supported["image/bmp"] = "bmp";
                $this->image_supported["image/x-ms-bmp"] = "bmp";
                $this->image_supported["image/x-windows-bmp"] = "bmp";
            }
        }
        if (empty($this->log)) {
            $this->log .= "<b>system information</b><br />";
            $inis = ini_get_all();
            $open_basedir = array_key_exists("open_basedir", $inis) && array_key_exists("local_value", $inis["open_basedir"]) && !empty($inis["open_basedir"]["local_value"]) ? $inis["open_basedir"]["local_value"] : false;
            $gd = $this->function_39() ? $this->function_39(true) : "GD not present";
            $supported = trim((in_array("png", $this->image_supported) ? "png" : "") . " " . (in_array("jpg", $this->image_supported) ? "jpg" : "") . " " . (in_array("gif", $this->image_supported) ? "gif" : "") . " " . (in_array("bmp", $this->image_supported) ? "bmp" : ""));
            $this->log .= "-&nbsp;class version           : " . $this->version . "<br />";
            $this->log .= "-&nbsp;operating system        : " . PHP_OS . "<br />";
            $this->log .= "-&nbsp;PHP version             : " . PHP_VERSION . "<br />";
            $this->log .= "-&nbsp;GD version              : " . $gd . "<br />";
            $this->log .= "-&nbsp;supported image types   : " . (!empty($supported) ? $supported : "none") . "<br />";
            $this->log .= "-&nbsp;open_basedir            : " . (!empty($open_basedir) ? $open_basedir : "no restriction") . "<br />";
            $this->log .= "-&nbsp;language                : " . $this->lang . "<br />";
        }
        if (!$file) {
            $this->$uploaded = false;
            $this->$error = $this->function_40("file_error");
        }
        if (!is_array($file)) {
            if (empty($file)) {
                $this->$uploaded = false;
                $this->$error = $this->function_40("file_error");
            } else {
                $this->$no_upload_check = true;
                $this->log .= "<b>" . $this->function_40("source is a local file") . " " . $file . "</b><br />";
                if ($this->uploaded && !file_exists($file)) {
                    $this->$uploaded = false;
                    $this->$error = $this->function_40("local_file_missing");
                }
                if ($this->uploaded && !is_readable($file)) {
                    $this->$uploaded = false;
                    $this->$error = $this->function_40("local_file_not_readable");
                }
                if ($this->uploaded) {
                    $this->$file_src_pathname = $file;
                    $this->$file_src_name = basename($file);
                    $this->log .= "- local file name OK<br />";
                    preg_match("/\\.([^\\.]*\$)/", $this->file_src_name, $extension);
                    if (is_array($extension) && 0 < sizeof($extension)) {
                        $this->$file_src_name_ext = strtolower($extension[1]);
                        $this->$file_src_name_body = substr($this->file_src_name, 0, strlen($this->file_src_name) - strlen($this->file_src_name_ext) - 1);
                    } else {
                        $this->$file_src_name_ext = "";
                        $this->$file_src_name_body = $this->file_src_name;
                    }
                    $this->$file_src_size = file_exists($file) ? filesize($file) : 0;
                }
                $this->$file_src_error = 0;
            }
        } else {
            $this->log .= "<b>source is an uploaded file</b><br />";
            if ($this->uploaded) {
                $this->$file_src_error = trim($file["error"]);
                switch ($this->file_src_error) {
                    case UPLOAD_ERR_OK:
                        $this->log .= "- upload OK<br />";
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_too_big_ini");
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_too_big_html");
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_partial");
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_missing");
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_no_tmp_dir");
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_cant_write");
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_err_extension");
                        break;
                    default:
                        $this->$uploaded = false;
                        $this->$error = $this->function_40("uploaded_unknown") . " (" . $this->file_src_error . ")";
                }
            }
            if ($this->uploaded) {
                $this->$file_src_pathname = $file["tmp_name"];
                $this->$file_src_name = $file["name"];
                if ($this->$file_src_name = = "") {
                    $this->$uploaded = false;
                    $this->$error = $this->function_40("try_again");
                }
            }
            if ($this->uploaded) {
                $this->log .= "- file name OK<br />";
                preg_match("/\\.([^\\.]*\$)/", $this->file_src_name, $extension);
                if (is_array($extension) && 0 < sizeof($extension)) {
                    $this->$file_src_name_ext = strtolower($extension[1]);
                    $this->$file_src_name_body = substr($this->file_src_name, 0, strlen($this->file_src_name) - strlen($this->file_src_name_ext) - 1);
                } else {
                    $this->$file_src_name_ext = "";
                    $this->$file_src_name_body = $this->file_src_name;
                }
                $this->$file_src_size = $file["size"];
                $mime_from_browser = $file["type"];
            }
        }
        if ($this->uploaded) {
            $this->log .= "<b>determining MIME type</b><br />";
            $this->$file_src_mime = NULL;
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, "/") === false) {
                if ($this->mime_fileinfo) {
                    $this->log .= "- Checking MIME type with Fileinfo PECL extension<br />";
                    if (function_exists("finfo_open")) {
                        if ($this->mime_fileinfo !== "") {
                            if ($this->$mime_fileinfo = == true) {
                                if (getenv("MAGIC") === false) {
                                    if (substr(PHP_OS, 0, 3) == "WIN") {
                                        $path = realpath(ini_get("extension_dir") . "/../") . "extras/magic";
                                    } else {
                                        $path = "/usr/share/file/magic";
                                    }
                                    $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path defaults to " . $path . "<br />";
                                } else {
                                    $path = getenv("MAGIC");
                                    $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path is set to " . $path . " from MAGIC variable<br />";
                                }
                            } else {
                                $path = $this->mime_fileinfo;
                                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path is set to " . $path . "<br />";
                            }
                            $f = @var_90(FILEINFO_MIME, $path);
                        } else {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MAGIC path will not be used<br />";
                            $f = @var_90(FILEINFO_MIME);
                        }
                        if (is_resource($f)) {
                            $mime = var_91($f, realpath($this->file_src_pathname));
                            var_92($f);
                            $this->$file_src_mime = $mime;
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as " . $this->file_src_mime . " by Fileinfo PECL extension<br />";
                            if (preg_match("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", $this->file_src_mime)) {
                                $this->$file_src_mime = preg_replace("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", "\$1/\$2", $this->file_src_mime);
                                $this->log .= "-&nbsp;MIME validated as " . $this->file_src_mime . "<br />";
                            } else {
                                $this->$file_src_mime = NULL;
                            }
                        } else {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension failed (finfo_open)<br />";
                        }
                    } else {
                        if (class_exists("finfo")) {
                            $f = new var_93(FILEINFO_MIME);
                            if ($f) {
                                $this->$file_src_mime = $f->var_94(realpath($this->file_src_pathname));
                                $this->log .= "- MIME type detected as " . $this->file_src_mime . " by Fileinfo PECL extension<br />";
                                if (preg_match("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", $this->file_src_mime)) {
                                    $this->$file_src_mime = preg_replace("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", "\$1/\$2", $this->file_src_mime);
                                    $this->log .= "-&nbsp;MIME validated as " . $this->file_src_mime . "<br />";
                                } else {
                                    $this->$file_src_mime = NULL;
                                }
                            } else {
                                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension failed (finfo)<br />";
                            }
                        } else {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;Fileinfo PECL extension not available<br />";
                        }
                    }
                } else {
                    $this->log .= "- Fileinfo PECL extension deactivated<br />";
                }
            }
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, "/") === false) {
                if ($this->mime_file) {
                    $this->log .= "- Checking MIME type with UNIX file() command<br />";
                    if (substr(PHP_OS, 0, 3) != "WIN") {
                        if (strlen($mime = @exec("file -bi " . @escapeshellarg($this->file_src_pathname))) != 0) {
                            $this->$file_src_mime = trim($mime);
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as " . $this->file_src_mime . " by UNIX file() command<br />";
                            if (preg_match("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", $this->file_src_mime)) {
                                $this->$file_src_mime = preg_replace("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", "\$1/\$2", $this->file_src_mime);
                                $this->log .= "-&nbsp;MIME validated as " . $this->file_src_mime . "<br />";
                            } else {
                                $this->$file_src_mime = NULL;
                            }
                        } else {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;UNIX file() command failed<br />";
                        }
                    } else {
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;UNIX file() command not availabled<br />";
                    }
                } else {
                    $this->log .= "- UNIX file() command is deactivated<br />";
                }
            }
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, "/") === false) {
                if ($this->mime_magic) {
                    $this->log .= "- Checking MIME type with mime.magic file (mime_content_type())<br />";
                    if (function_exists("mime_content_type")) {
                        $this->$file_src_mime = var_95($this->file_src_pathname);
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as " . $this->file_src_mime . " by mime_content_type()<br />";
                        if (preg_match("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", $this->file_src_mime)) {
                            $this->$file_src_mime = preg_replace("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", "\$1/\$2", $this->file_src_mime);
                            $this->log .= "-&nbsp;MIME validated as " . $this->file_src_mime . "<br />";
                        } else {
                            $this->$file_src_mime = NULL;
                        }
                    } else {
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;mime_content_type() is not available<br />";
                    }
                } else {
                    $this->log .= "- mime.magic file (mime_content_type()) is deactivated<br />";
                }
            }
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, "/") === false) {
                if ($this->mime_getimagesize) {
                    $this->log .= "- Checking MIME type with getimagesize()<br />";
                    $info = getimagesize($this->file_src_pathname);
                    if (is_array($info) && array_key_exists("mime", $info)) {
                        $this->$file_src_mime = trim($info["mime"]);
                        if (empty($this->file_src_mime)) {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MIME empty, guessing from type<br />";
                            $mime = is_array($info) && array_key_exists(2, $info) ? $info[2] : NULL;
                            $this->$file_src_mime = $mime == IMAGETYPE_GIF ? "image/gif" : ($mime == IMAGETYPE_JPEG ? "image/jpeg" : ($mime == IMAGETYPE_PNG ? "image/png" : ($mime == IMAGETYPE_BMP ? "image/bmp" : NULL)));
                        }
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;MIME type detected as " . $this->file_src_mime . " by PHP getimagesize() function<br />";
                        if (preg_match("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", $this->file_src_mime)) {
                            $this->$file_src_mime = preg_replace("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", "\$1/\$2", $this->file_src_mime);
                            $this->log .= "-&nbsp;MIME validated as " . $this->file_src_mime . "<br />";
                        } else {
                            $this->$file_src_mime = NULL;
                        }
                    } else {
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;getimagesize() failed<br />";
                    }
                } else {
                    $this->log .= "- getimagesize() is deactivated<br />";
                }
            }
            if (!empty($mime_from_browser) && !$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime)) {
                $this->$file_src_mime = $mime_from_browser;
                $this->log .= "- MIME type detected as " . $this->file_src_mime . " by browser<br />";
                if (preg_match("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", $this->file_src_mime)) {
                    $this->$file_src_mime = preg_replace("/^([\\.-\\w]+)\\/([\\.-\\w]+)(.*)\$/i", "\$1/\$2", $this->file_src_mime);
                    $this->log .= "-&nbsp;MIME validated as " . $this->file_src_mime . "<br />";
                } else {
                    $this->$file_src_mime = NULL;
                }
            }
            if ($this->$file_src_mime = = "application/octet-stream" || !$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, "/") === false) {
                if ($this->$file_src_mime = = "application/octet-stream") {
                    $this->log .= "- Flash may be rewriting MIME as application/octet-stream<br />";
                }
                $this->log .= "- Try to guess MIME type from file extension (" . $this->file_src_name_ext . "): ";
                switch ($this->file_src_name_ext) {
                    case "jpg":
                    case "jpeg":
                    case "jpe":
                        $this->$file_src_mime = "image/jpeg";
                        break;
                    case "gif":
                        $this->$file_src_mime = "image/gif";
                        break;
                    case "png":
                        $this->$file_src_mime = "image/png";
                        break;
                    case "bmp":
                        $this->$file_src_mime = "image/bmp";
                        break;
                    case "flv":
                        $this->$file_src_mime = "video/x-flv";
                        break;
                    case "js":
                        $this->$file_src_mime = "application/x-javascript";
                        break;
                    case "json":
                        $this->$file_src_mime = "application/json";
                        break;
                    case "tiff":
                        $this->$file_src_mime = "image/tiff";
                        break;
                    case "css":
                        $this->$file_src_mime = "text/css";
                        break;
                    case "xml":
                        $this->$file_src_mime = "application/xml";
                        break;
                    case "doc":
                    case "docx":
                        $this->$file_src_mime = "application/msword";
                        break;
                    case "xls":
                    case "xlt":
                    case "xlm":
                    case "xld":
                    case "xla":
                    case "xlc":
                    case "xlw":
                    case "xll":
                        $this->$file_src_mime = "application/vnd.ms-excel";
                        break;
                    case "ppt":
                    case "pps":
                        $this->$file_src_mime = "application/vnd.ms-powerpoint";
                        break;
                    case "rtf":
                        $this->$file_src_mime = "application/rtf";
                        break;
                    case "pdf":
                        $this->$file_src_mime = "application/pdf";
                        break;
                    case "html":
                    case "htm":
                    case "php":
                        $this->$file_src_mime = "text/html";
                        break;
                    case "txt":
                        $this->$file_src_mime = "text/plain";
                        break;
                    case "mpeg":
                    case "mpg":
                    case "mpe":
                        $this->$file_src_mime = "video/mpeg";
                        break;
                    case "mp3":
                        $this->$file_src_mime = "audio/mpeg3";
                        break;
                    case "wav":
                        $this->$file_src_mime = "audio/wav";
                        break;
                    case "aiff":
                    case "aif":
                        $this->$file_src_mime = "audio/aiff";
                        break;
                    case "avi":
                        $this->$file_src_mime = "video/msvideo";
                        break;
                    case "wmv":
                        $this->$file_src_mime = "video/x-ms-wmv";
                        break;
                    case "mov":
                        $this->$file_src_mime = "video/quicktime";
                        break;
                    case "zip":
                        $this->$file_src_mime = "application/zip";
                        break;
                    case "tar":
                        $this->$file_src_mime = "application/x-tar";
                        break;
                    case "swf":
                        $this->$file_src_mime = "application/x-shockwave-flash";
                        break;
                    case "odt":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.text";
                        break;
                    case "ott":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.text-template";
                        break;
                    case "oth":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.text-web";
                        break;
                    case "odm":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.text-master";
                        break;
                    case "odg":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.graphics";
                        break;
                    case "otg":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.graphics-template";
                        break;
                    case "odp":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.presentation";
                        break;
                    case "otp":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.presentation-template";
                        break;
                    case "ods":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.spreadsheet";
                        break;
                    case "ots":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.spreadsheet-template";
                        break;
                    case "odc":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.chart";
                        break;
                    case "odf":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.formula";
                        break;
                    case "odb":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.database";
                        break;
                    case "odi":
                        $this->$file_src_mime = "application/vnd.oasis.opendocument.image";
                        break;
                    case "oxt":
                        $this->$file_src_mime = "application/vnd.openofficeorg.extension";
                        break;
                    case "docx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                        break;
                    case "docm":
                        $this->$file_src_mime = "application/vnd.ms-word.document.macroEnabled.12";
                        break;
                    case "dotx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
                        break;
                    case "dotm":
                        $this->$file_src_mime = "application/vnd.ms-word.template.macroEnabled.12";
                        break;
                    case "xlsx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                        break;
                    case "xlsm":
                        $this->$file_src_mime = "application/vnd.ms-excel.sheet.macroEnabled.12";
                        break;
                    case "xltx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
                        break;
                    case "xltm":
                        $this->$file_src_mime = "application/vnd.ms-excel.template.macroEnabled.12";
                        break;
                    case "xlsb":
                        $this->$file_src_mime = "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
                        break;
                    case "xlam":
                        $this->$file_src_mime = "application/vnd.ms-excel.addin.macroEnabled.12";
                        break;
                    case "pptx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
                        break;
                    case "pptm":
                        $this->$file_src_mime = "application/vnd.ms-powerpoint.presentation.macroEnabled.12";
                        break;
                    case "ppsx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
                        break;
                    case "ppsm":
                        $this->$file_src_mime = "application/vnd.ms-powerpoint.slideshow.macroEnabled.12";
                        break;
                    case "potx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.presentationml.template";
                        break;
                    case "potm":
                        $this->$file_src_mime = "application/vnd.ms-powerpoint.template.macroEnabled.12";
                        break;
                    case "ppam":
                        $this->$file_src_mime = "application/vnd.ms-powerpoint.addin.macroEnabled.12";
                        break;
                    case "sldx":
                        $this->$file_src_mime = "application/vnd.openxmlformats-officedocument.presentationml.slide";
                        break;
                    case "sldm":
                        $this->$file_src_mime = "application/vnd.ms-powerpoint.slide.macroEnabled.12";
                        break;
                    case "thmx":
                        $this->$file_src_mime = "application/vnd.ms-officetheme";
                        break;
                    case "onetoc":
                    case "onetoc2":
                    case "onetmp":
                    case "onepkg":
                        $this->$file_src_mime = "application/onenote";
                        break;
                    default:
                        if ($this->$file_src_mime = = "application/octet-stream") {
                            $this->log .= "doesn't look like anything known<br />";
                        } else {
                            $this->log .= "MIME type set to " . $this->file_src_mime . "<br />";
                        }
                }
            }
            if (!$this->file_src_mime || !is_string($this->file_src_mime) || empty($this->file_src_mime) || strpos($this->file_src_mime, "/") === false) {
                $this->log .= "- MIME type couldn't be detected! (" . (string) $this->file_src_mime . ")<br />";
            }
            if ($this->file_src_mime && is_string($this->file_src_mime) && !empty($this->file_src_mime) && array_key_exists($this->file_src_mime, $this->image_supported)) {
                $this->$file_is_image = true;
                $this->$image_src_type = $this->image_supported[$this->file_src_mime];
            }
            if ($this->file_is_image) {
                if ($h = fopen($this->file_src_pathname, "r")) {
                    fclose($h);
                    $info = getimagesize($this->file_src_pathname);
                    if (is_array($info)) {
                        list($this->image_src_x, $this->image_src_y) = $info;
                        $this->$image_dst_x = $this->image_src_x;
                        $this->$image_dst_y = $this->image_src_y;
                        $this->$image_src_pixels = $this->image_src_x * $this->image_src_y;
                        $this->$image_src_bits = array_key_exists("bits", $info) ? $info["bits"] : NULL;
                    } else {
                        $this->$file_is_image = false;
                        $this->$uploaded = false;
                        $this->log .= "- can't retrieve image information, image may have been tampered with<br />";
                        $this->$error = $this->function_40("incorrect_file");
                    }
                } else {
                    $this->log .= "- can't read source file directly. open_basedir restriction in place?<br />";
                }
            }
            $this->log .= "<b>source variables</b><br />";
            $this->log .= "- You can use all these before calling process()<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_name         : " . $this->file_src_name . "<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_body    : " . $this->file_src_name_body . "<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_ext     : " . $this->file_src_name_ext . "<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_pathname     : " . $this->file_src_pathname . "<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_mime         : " . $this->file_src_mime . "<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_size         : " . $this->file_src_size . " ($max = " . $this->file_max_size . ")<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_src_error        : " . $this->file_src_error . "<br />";
            if ($this->file_is_image) {
                $this->log .= "- source file is an image<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_src_x           : " . $this->image_src_x . "<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_src_y           : " . $this->image_src_y . "<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_src_pixels      : " . $this->image_src_pixels . "<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_src_type        : " . $this->image_src_type . "<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_src_bits        : " . $this->image_src_bits . "<br />";
            }
        }
    }
    public function function_39($full = false)
    {
        if ($var_96 === NULL) {
            if (function_exists("gd_info")) {
                $gd = gd_info();
                $gd = $gd["GD Version"];
                $var_97 = "/([\\d\\.]+)/i";
            } else {
                ob_start();
                phpinfo(8);
                $gd = ob_get_contents();
                ob_end_clean();
                $var_97 = "/\\bgd\\s+version\\b[^\\d\n\r]+?([\\d\\.]+)/i";
            }
            if (preg_match($var_97, $gd, $m)) {
                $var_98 = (string) $m[1];
                $var_96 = (double) $m[1];
            } else {
                $var_98 = "none";
                $var_96 = 0;
            }
        }
        if ($full) {
            return $var_98;
        }
        return $var_96;
    }
    public function function_41($path, $mode = 511)
    {
        return is_dir($path) || $this->function_41(dirname($path), $mode) && $this->function_42($path, $mode);
    }
    public function function_42($path, $mode = 511)
    {
        $var_99 = umask(0);
        $res = @mkdir($path, $mode);
        umask($var_99);
        return $res;
    }
    public function function_40($str, $tokens = [])
    {
        if (array_key_exists($str, $this->translation)) {
            $str = $this->translation[$str];
        }
        if (is_array($tokens) && 0 < sizeof($tokens)) {
            $str = vsprintf($str, $tokens);
        }
        return $str;
    }
    public function function_43($color)
    {
        $var_100 = sscanf($color, "#%2x%2x%2x");
        $var_101 = array_key_exists(0, $var_100) && is_numeric($var_100[0]) ? $var_100[0] : 0;
        $var_102 = array_key_exists(1, $var_100) && is_numeric($var_100[1]) ? $var_100[1] : 0;
        $var_103 = array_key_exists(2, $var_100) && is_numeric($var_100[2]) ? $var_100[2] : 0;
        return [$var_101, $var_102, $var_103];
    }
    public function function_44($x, $y, $fill = true, $trsp = false)
    {
        if ($x < 1) {
            $x = 1;
        }
        if ($y < 1) {
            $y = 1;
        }
        if (2 <= $this->function_39() && !$this->image_is_palette) {
            $var_104 = imagecreatetruecolor($x, $y);
            if (empty($this->image_background_color) || $trsp) {
                imagealphablending($var_104, false);
                imagefilledrectangle($var_104, 0, 0, $x, $y, imagecolorallocatealpha($var_104, 0, 0, 0, 127));
            }
        } else {
            $var_104 = imagecreate($x, $y);
            if ($fill && $this->image_is_transparent && empty($this->image_background_color) || $trsp) {
                imagefilledrectangle($var_104, 0, 0, $x, $y, $this->image_transparent_color);
                imagecolortransparent($var_104, $this->image_transparent_color);
            }
        }
        if ($fill && !empty($this->image_background_color) && !$trsp) {
            list($var_101, $var_102, $var_103) = $this->function_43($this->image_background_color);
            $var_105 = imagecolorallocate($var_104, $var_101, $var_102, $var_103);
            imagefilledrectangle($var_104, 0, 0, $x, $y, $var_105);
        }
        return $var_104;
    }
    public function function_45($src_im, $dst_im)
    {
        if (is_resource($dst_im)) {
            imagedestroy($dst_im);
        }
        $dst_im =& $src_im;
        return $dst_im;
    }
    public function function_46(&$dst_im, &$src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct = 0)
    {
        $dst_x = (int) $dst_x;
        $dst_y = (int) $dst_y;
        $src_x = (int) $src_x;
        $src_y = (int) $src_y;
        $src_w = (int) $src_w;
        $src_h = (int) $src_h;
        $pct = (int) $pct;
        $var_106 = imagesx($dst_im);
        $var_107 = imagesy($dst_im);
        for ($y = $src_y; $y < $src_h; $y++) {
            for ($x = $src_x; $x < $src_w; $x++) {
                if (0 <= $x + $dst_x && $x + $dst_x < $var_106 && 0 <= $x + $src_x && $x + $src_x < $src_w && 0 <= $y + $dst_y && $y + $dst_y < $var_107 && 0 <= $y + $src_y && $y + $src_y < $src_h) {
                    $var_108 = imagecolorsforindex($dst_im, imagecolorat($dst_im, $x + $dst_x, $y + $dst_y));
                    $var_109 = imagecolorsforindex($src_im, imagecolorat($src_im, $x + $src_x, $y + $src_y));
                    $var_110 = 1 - $var_109["alpha"] / 127;
                    $var_111 = 1 - $var_108["alpha"] / 127;
                    $var_112 = $var_110 * $pct / 100;
                    if ($var_112 <= $var_111) {
                        $var_113 = $var_111;
                    }
                    if ($var_111 < $var_112) {
                        $var_113 = $var_112;
                    }
                    if (1 < $var_113) {
                        $var_113 = 1;
                    }
                    if (0 < $var_112) {
                        $var_114 = round($var_108["red"] * $var_111 * (1 - $var_112));
                        $var_115 = round($var_108["green"] * $var_111 * (1 - $var_112));
                        $var_116 = round($var_108["blue"] * $var_111 * (1 - $var_112));
                        $var_117 = round($var_109["red"] * $var_112);
                        $var_118 = round($var_109["green"] * $var_112);
                        $var_119 = round($var_109["blue"] * $var_112);
                        $var_101 = round(($var_114 + $var_117) / ($var_111 * (1 - $var_112) + $var_112));
                        $var_102 = round(($var_115 + $var_118) / ($var_111 * (1 - $var_112) + $var_112));
                        $var_103 = round(($var_116 + $var_119) / ($var_111 * (1 - $var_112) + $var_112));
                        if (255 < $var_101) {
                            $var_101 = 255;
                        }
                        if (255 < $var_102) {
                            $var_102 = 255;
                        }
                        if (255 < $var_103) {
                            $var_103 = 255;
                        }
                        $var_113 = round((1 - $var_113) * 127);
                        $color = imagecolorallocatealpha($dst_im, $var_101, $var_102, $var_103, $var_113);
                        imagesetpixel($dst_im, $x + $dst_x, $y + $dst_y, $color);
                    }
                }
            }
        }
        return true;
    }
    public function function_47($server_path = NULL)
    {
        $this->$error = "";
        $this->$processed = true;
        $var_120 = false;
        $var_121 = NULL;
        if (!$this->uploaded) {
            $this->$error = $this->function_40("file_not_uploaded");
            $this->$processed = false;
        }
        if ($this->processed) {
            if (empty($server_path) || is_null($server_path)) {
                $this->log .= "<b>process file and return the content</b><br />";
                $var_120 = true;
            } else {
                if (strtolower(substr(PHP_OS, 0, 3)) === "win") {
                    if (substr($server_path, -1, 1) != "\\") {
                        $server_path = $server_path . "\\";
                    }
                } else {
                    if (substr($server_path, -1, 1) != "/") {
                        $server_path = $server_path . "/";
                    }
                }
                $this->log .= "<b>process file to " . $server_path . "</b><br />";
            }
        }
        if ($this->processed) {
            if ($this->file_max_size < $this->file_src_size) {
                $this->$processed = false;
                $this->$error = $this->function_40("file_too_big");
            } else {
                $this->log .= "- file size OK<br />";
            }
        }
        if ($this->processed) {
            if ($this->no_script && ((substr($this->file_src_mime, 0, 5) == "text/" || strpos($this->file_src_mime, "javascript") !== false) && substr($this->file_src_name, -4) != ".txt" || preg_match("/\\.(php|pl|py|cgi|asp)\$/i", $this->file_src_name) || empty($this->file_src_name_ext))) {
                $this->$file_src_mime = "text/plain";
                $this->log .= "- script " . $this->file_src_name . " renamed as " . $this->file_src_name . ".txt!<br />";
                $this->file_src_name_ext .= empty($this->file_src_name_ext) ? "txt" : ".txt";
            }
            if ($this->mime_check && empty($this->file_src_mime)) {
                $this->$processed = false;
                $this->$error = $this->function_40("no_mime");
            } else {
                if ($this->mime_check && !empty($this->file_src_mime) && strpos($this->file_src_mime, "/") !== false) {
                    list($var_122, $var_123) = explode("/", $this->file_src_mime);
                    $allowed = false;
                    foreach ($this->allowed as $k => $var_124) {
                        list($var_125, $var_126) = explode("/", $var_124);
                        if ($var_125 == "*" && $var_126 == "*" || $var_125 == $var_122 && ($var_126 == $var_123 || $var_126 == "*")) {
                            $allowed = true;
                            foreach ($this->forbidden as $k => $var_124) {
                                list($var_125, $var_126) = explode("/", $var_124);
                                if ($var_125 == "*" && $var_126 == "*" || $var_125 == $var_122 && ($var_126 == $var_123 || $var_126 == "*")) {
                                    $allowed = false;
                                    if (!$allowed) {
                                        $this->$processed = false;
                                        $this->$error = $this->function_40("incorrect_file");
                                    } else {
                                        $this->log .= "- file mime OK : " . $this->file_src_mime . "<br />";
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $this->log .= "- file mime (not checked) : " . $this->file_src_mime . "<br />";
                }
            }
            if ($this->file_is_image) {
                if (is_numeric($this->image_src_x) && is_numeric($this->image_src_y)) {
                    $var_127 = $this->image_src_x / $this->image_src_y;
                    if (!is_null($this->image_max_width) && $this->image_max_width < $this->image_src_x) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("image_too_wide");
                    }
                    if (!is_null($this->image_min_width) && $this->image_src_x < $this->image_min_width) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("image_too_narrow");
                    }
                    if (!is_null($this->image_max_height) && $this->image_max_height < $this->image_src_y) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("image_too_high");
                    }
                    if (!is_null($this->image_min_height) && $this->image_src_y < $this->image_min_height) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("image_too_short");
                    }
                    if (!is_null($this->image_max_ratio) && $this->image_max_ratio < $var_127) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("ratio_too_high");
                    }
                    if (!is_null($this->image_min_ratio) && $var_127 < $this->image_min_ratio) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("ratio_too_low");
                    }
                    if (!is_null($this->image_max_pixels) && $this->image_max_pixels < $this->image_src_pixels) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("too_many_pixels");
                    }
                    if (!is_null($this->image_min_pixels) && $this->image_src_pixels < $this->image_min_pixels) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("not_enough_pixels");
                    }
                } else {
                    $this->log .= "- no image properties available, can't enforce dimension checks : " . $this->file_src_mime . "<br />";
                }
            }
        }
        if ($this->processed) {
            $this->$file_dst_path = $server_path;
            $this->$file_dst_name = $this->file_src_name;
            $this->$file_dst_name_body = $this->file_src_name_body;
            $this->$file_dst_name_ext = $this->file_src_name_ext;
            if ($this->file_overwrite) {
                $this->$file_auto_rename = false;
            }
            if ($this->image_convert != "") {
                $this->$file_dst_name_ext = $this->image_convert;
                $this->log .= "- new file name ext : " . $this->image_convert . "<br />";
            }
            if ($this->file_new_name_body != "") {
                $this->$file_dst_name_body = $this->file_new_name_body;
                $this->log .= "- new file name body : " . $this->file_new_name_body . "<br />";
            }
            if ($this->file_new_name_ext != "") {
                $this->$file_dst_name_ext = $this->file_new_name_ext;
                $this->log .= "- new file name ext : " . $this->file_new_name_ext . "<br />";
            }
            if ($this->file_name_body_add != "") {
                $this->$file_dst_name_body = $this->file_dst_name_body . $this->file_name_body_add;
                $this->log .= "- file name body append : " . $this->file_name_body_add . "<br />";
            }
            if ($this->file_name_body_pre != "") {
                $this->$file_dst_name_body = $this->file_name_body_pre . $this->file_dst_name_body;
                $this->log .= "- file name body prepend : " . $this->file_name_body_pre . "<br />";
            }
            if ($this->file_safe_name) {
                $this->$file_dst_name_body = str_replace([" ", "-"], ["_", "_"], $this->file_dst_name_body);
                $this->$file_dst_name_body = preg_replace("/[^A-Za-z0-9_]/", "", $this->file_dst_name_body);
                $this->log .= "- file name safe format<br />";
            }
            $this->log .= "- destination variables<br />";
            if (empty($this->file_dst_path) || is_null($this->file_dst_path)) {
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path         : n/a<br />";
            } else {
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path         : " . $this->file_dst_path . "<br />";
            }
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_body    : " . $this->file_dst_name_body . "<br />";
            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_ext     : " . $this->file_dst_name_ext . "<br />";
            $var_128 = $this->file_is_image && ($this->image_resize || $this->image_convert != "" || is_numeric($this->image_brightness) || is_numeric($this->image_contrast) || is_numeric($this->image_threshold) || !empty($this->image_tint_color) || !empty($this->image_overlay_color) || !empty($this->image_text) || $this->image_greyscale || $this->image_negative || !empty($this->image_watermark) || is_numeric($this->image_rotate) || is_numeric($this->jpeg_size) || !empty($this->image_flip) || !empty($this->image_crop) || !empty($this->image_precrop) || !empty($this->image_border) || 0 < $this->image_frame || 0 < $this->image_bevel || $this->image_reflection_height);
            if ($var_128) {
                if ($this->$image_convert = = "") {
                    $this->$file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? "." . $this->file_dst_name_ext : "");
                    $this->log .= "- image operation, keep extension<br />";
                } else {
                    $this->$file_dst_name = $this->file_dst_name_body . "." . $this->image_convert;
                    $this->log .= "- image operation, change extension for conversion type<br />";
                }
            } else {
                $this->$file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? "." . $this->file_dst_name_ext : "");
                $this->log .= "- no image operation, keep extension<br />";
            }
            if (!$var_120) {
                if (!$this->file_auto_rename) {
                    $this->log .= "- no auto_rename if same filename exists<br />";
                    $this->$file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                } else {
                    $this->log .= "- checking for auto_rename<br />";
                    $this->$file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                    $var_129 = $this->file_dst_name_body;
                    $var_130 = 1;
                    while (@file_exists($this->file_dst_pathname)) {
                        $this->$file_dst_name_body = $var_129 . "_" . $var_130;
                        $this->$file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? "." . $this->file_dst_name_ext : "");
                        $var_130++;
                        $this->$file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                    }
                    if (1 < $var_130) {
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;auto_rename to " . $this->file_dst_name . "<br />";
                    }
                }
                $this->log .= "- destination file details<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name         : " . $this->file_dst_name . "<br />";
                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;file_dst_pathname     : " . $this->file_dst_pathname . "<br />";
                if ($this->file_overwrite) {
                    $this->log .= "- no overwrite checking<br />";
                } else {
                    if (@file_exists($this->file_dst_pathname)) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("already_exists", [$this->file_dst_name]);
                    } else {
                        $this->log .= "- " . $this->file_dst_name . " doesn't exist already<br />";
                    }
                }
            }
        }
        if ($this->processed) {
            if (!empty($this->file_src_temp)) {
                $this->log .= "- use the temp file instead of the original file since it is a second process<br />";
                $this->$file_src_pathname = $this->file_src_temp;
                if (!file_exists($this->file_src_pathname)) {
                    $this->$processed = false;
                    $this->$error = $this->function_40("temp_file_missing");
                }
            } else {
                if (!$this->no_upload_check) {
                    if (!is_uploaded_file($this->file_src_pathname)) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("source_missing");
                    }
                } else {
                    if (!file_exists($this->file_src_pathname)) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("source_missing");
                    }
                }
            }
            if (!$var_120) {
                if ($this->processed && !file_exists($this->file_dst_path)) {
                    if ($this->dir_auto_create) {
                        $this->log .= "- " . $this->file_dst_path . " doesn't exist. Attempting creation:";
                        if (!$this->function_41($this->file_dst_path, $this->dir_chmod)) {
                            $this->log .= " failed<br />";
                            $this->$processed = false;
                            $this->$error = $this->function_40("destination_dir");
                        } else {
                            $this->log .= " success<br />";
                        }
                    } else {
                        $this->$error = $this->function_40("destination_dir_missing");
                    }
                }
                if ($this->processed && !is_dir($this->file_dst_path)) {
                    $this->$processed = false;
                    $this->$error = $this->function_40("destination_path_not_dir");
                }
                $hash = md5($this->file_dst_name_body . rand(1, 1000));
                if ($this->processed && !($f = @fopen($this->file_dst_path . $hash . "." . $this->file_dst_name_ext, "a+"))) {
                    if ($this->dir_auto_chmod) {
                        $this->log .= "- " . $this->file_dst_path . " is not writeable. Attempting chmod:";
                        if (!@chmod($this->file_dst_path, $this->dir_chmod)) {
                            $this->log .= " failed<br />";
                            $this->$processed = false;
                            $this->$error = $this->function_40("destination_dir_write");
                        } else {
                            $this->log .= " success<br />";
                            if (!($f = @fopen($this->file_dst_path . $hash . "." . $this->file_dst_name_ext, "a+"))) {
                                $this->$processed = false;
                                $this->$error = $this->function_40("destination_dir_write");
                            } else {
                                @fclose($f);
                            }
                        }
                    } else {
                        $this->$processed = false;
                        $this->$error = $this->function_40("destination_path_write");
                    }
                } else {
                    if ($this->processed) {
                        @fclose($f);
                    }
                    @unlink($this->file_dst_path . $hash . "." . $this->file_dst_name_ext);
                }
                if (!$this->no_upload_check && empty($this->file_src_temp) && !@file_exists($this->file_src_pathname)) {
                    $this->log .= "- attempting to use a temp file:";
                    $hash = md5($this->file_dst_name_body . rand(1, 1000));
                    if (move_uploaded_file($this->file_src_pathname, $this->file_dst_path . $hash . "." . $this->file_dst_name_ext)) {
                        $this->$file_src_pathname = $this->file_dst_path . $hash . "." . $this->file_dst_name_ext;
                        $this->$file_src_temp = $this->file_src_pathname;
                        $this->log .= " file created<br />";
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;temp file is: " . $this->file_src_temp . "<br />";
                    } else {
                        $this->log .= " failed<br />";
                        $this->$processed = false;
                        $this->$error = $this->function_40("temp_file");
                    }
                }
            }
        }
        if ($this->processed) {
            if ($var_128 && !@getimagesize($this->file_src_pathname)) {
                $this->log .= "- the file is not an image!<br />";
                $var_128 = false;
            }
            if ($var_128) {
                if ($this->processed && !($f = @fopen($this->file_src_pathname, "r"))) {
                    $this->$processed = false;
                    $this->$error = $this->function_40("source_not_readable");
                } else {
                    @fclose($f);
                }
                $this->log .= "- image resizing or conversion wanted<br />";
                if ($this->function_39()) {
                    switch ($this->image_src_type) {
                        case "jpg":
                            if (!function_exists("imagecreatefromjpeg")) {
                                $this->$processed = false;
                                $this->$error = $this->function_40("no_create_support", ["JPEG"]);
                            } else {
                                $var_131 = @imagecreatefromjpeg($this->file_src_pathname);
                                if (!$var_131) {
                                    $this->$processed = false;
                                    $this->$error = $this->function_40("create_error", ["JPEG"]);
                                } else {
                                    $this->log .= "- source image is JPEG<br />";
                                }
                            }
                            break;
                        case "png":
                            if (!function_exists("imagecreatefrompng")) {
                                $this->$processed = false;
                                $this->$error = $this->function_40("no_create_support", ["PNG"]);
                            } else {
                                $var_131 = @imagecreatefrompng($this->file_src_pathname);
                                if (!$var_131) {
                                    $this->$processed = false;
                                    $this->$error = $this->function_40("create_error", ["PNG"]);
                                } else {
                                    $this->log .= "- source image is PNG<br />";
                                }
                            }
                            break;
                        case "gif":
                            if (!function_exists("imagecreatefromgif")) {
                                $this->$processed = false;
                                $this->$error = $this->function_40("no_create_support", ["GIF"]);
                            } else {
                                $var_131 = @imagecreatefromgif($this->file_src_pathname);
                                if (!$var_131) {
                                    $this->$processed = false;
                                    $this->$error = $this->function_40("create_error", ["GIF"]);
                                } else {
                                    $this->log .= "- source image is GIF<br />";
                                }
                            }
                            break;
                        case "bmp":
                            if (!method_exists($this, "imagecreatefrombmp")) {
                                $this->$processed = false;
                                $this->$error = $this->function_40("no_create_support", ["BMP"]);
                            } else {
                                $var_131 = @$this->function_48($this->file_src_pathname);
                                if (!$var_131) {
                                    $this->$processed = false;
                                    $this->$error = $this->function_40("create_error", ["BMP"]);
                                } else {
                                    $this->log .= "- source image is BMP<br />";
                                }
                            }
                            break;
                        default:
                            $this->$processed = false;
                            $this->$error = $this->function_40("source_invalid");
                    }
                } else {
                    $this->$processed = false;
                    $this->$error = $this->function_40("gd_missing");
                }
                if ($this->processed && $var_131) {
                    if (empty($this->image_convert)) {
                        $this->log .= "- setting destination file type to " . $this->file_src_name_ext . "<br />";
                        $this->$image_convert = $this->file_src_name_ext;
                    }
                    if (!in_array($this->image_convert, $this->image_supported)) {
                        $this->$image_convert = "jpg";
                    }
                    if ($this->image_convert != "png" && $this->image_convert != "gif" && !empty($this->image_default_color) && empty($this->image_background_color)) {
                        $this->$image_background_color = $this->image_default_color;
                    }
                    if (!empty($this->image_background_color)) {
                        $this->$image_default_color = $this->image_background_color;
                    }
                    if (empty($this->image_default_color)) {
                        $this->$image_default_color = "#FFFFFF";
                    }
                    $this->$image_src_x = imagesx($var_131);
                    $this->$image_src_y = imagesy($var_131);
                    $var_96 = $this->function_39();
                    $cropOffsets = NULL;
                    if (!imageistruecolor($var_131)) {
                        $this->log .= "- image is detected as having a palette<br />";
                        $this->$image_is_palette = true;
                        $this->$image_transparent_color = imagecolortransparent($var_131);
                        if (0 <= $this->image_transparent_color && $this->image_transparent_color < imagecolorstotal($var_131)) {
                            $this->$image_is_transparent = true;
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;palette image is detected as transparent<br />";
                        }
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;convert palette image to true color<br />";
                        $var_133 = imagecreatetruecolor($this->image_src_x, $this->image_src_y);
                        imagealphablending($var_133, false);
                        imagesavealpha($var_133, true);
                        for ($x = 0; $x < $this->image_src_x; $x++) {
                            for ($y = 0; $y < $this->image_src_y; $y++) {
                                if (0 <= $this->image_transparent_color && imagecolorat($var_131, $x, $y) == $this->image_transparent_color) {
                                    imagesetpixel($var_133, $x, $y, 2130706432);
                                } else {
                                    $var_134 = imagecolorsforindex($var_131, imagecolorat($var_131, $x, $y));
                                    imagesetpixel($var_133, $x, $y, $var_134["alpha"] << 24 | $var_134["red"] << 16 | $var_134["green"] << 8 | $var_134["blue"]);
                                }
                            }
                        }
                        $var_131 = $this->function_45($var_133, $var_131);
                        imagealphablending($var_131, false);
                        imagesavealpha($var_131, true);
                        $this->$image_is_palette = false;
                    }
                    $workingImage =& $var_131;
                    if (!empty($this->image_precrop)) {
                        if (is_array($this->image_precrop)) {
                            $vars = $this->image_precrop;
                        } else {
                            $vars = explode(" ", $this->image_precrop);
                        }
                        if (sizeof($vars) == 4) {
                            list($var_136, $var_137, $var_138, $var_139) = $vars;
                        } else {
                            if (sizeof($vars) == 2) {
                                $var_136 = $vars[0];
                                $var_137 = $vars[1];
                                $var_138 = $vars[0];
                                $var_139 = $vars[1];
                            } else {
                                $var_136 = $vars[0];
                                $var_137 = $vars[0];
                                $var_138 = $vars[0];
                                $var_139 = $vars[0];
                            }
                        }
                        if (0 < strpos($var_136, "%")) {
                            $var_136 = $this->image_src_y * str_replace("%", "", $var_136) / 100;
                        }
                        if (0 < strpos($var_137, "%")) {
                            $var_137 = $this->image_src_x * str_replace("%", "", $var_137) / 100;
                        }
                        if (0 < strpos($var_138, "%")) {
                            $var_138 = $this->image_src_y * str_replace("%", "", $var_138) / 100;
                        }
                        if (0 < strpos($var_139, "%")) {
                            $var_139 = $this->image_src_x * str_replace("%", "", $var_139) / 100;
                        }
                        if (0 < strpos($var_136, "px")) {
                            $var_136 = str_replace("px", "", $var_136);
                        }
                        if (0 < strpos($var_137, "px")) {
                            $var_137 = str_replace("px", "", $var_137);
                        }
                        if (0 < strpos($var_138, "px")) {
                            $var_138 = str_replace("px", "", $var_138);
                        }
                        if (0 < strpos($var_139, "px")) {
                            $var_139 = str_replace("px", "", $var_139);
                        }
                        $var_136 = (int) $var_136;
                        $var_137 = (int) $var_137;
                        $var_138 = (int) $var_138;
                        $var_139 = (int) $var_139;
                        $this->log .= "- pre-crop image : " . $var_136 . " " . $var_137 . " " . $var_138 . " " . $var_139 . " <br />";
                        $this->$image_src_x = $this->image_src_x - $var_139 - $var_137;
                        $this->$image_src_y = $this->image_src_y - $var_136 - $var_138;
                        if ($this->image_src_x < 1) {
                            $this->$image_src_x = 1;
                        }
                        if ($this->image_src_y < 1) {
                            $this->$image_src_y = 1;
                        }
                        $tempImage = $this->function_44($this->image_src_x, $this->image_src_y);
                        imagecopy($tempImage, $workingImage, 0, 0, $var_139, $var_136, $this->image_src_x, $this->image_src_y);
                        if ($var_136 < 0 || $var_137 < 0 || $var_138 < 0 || $var_139 < 0) {
                            if (!empty($this->image_background_color)) {
                                list($var_101, $var_102, $var_103) = $this->function_43($this->image_background_color);
                                $fill = imagecolorallocate($tempImage, $var_101, $var_102, $var_103);
                            } else {
                                $fill = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
                            }
                            if ($var_136 < 0) {
                                imagefilledrectangle($tempImage, 0, 0, $this->image_src_x, -1 * $var_136, $fill);
                            }
                            if ($var_137 < 0) {
                                imagefilledrectangle($tempImage, $this->image_src_x + $var_137, 0, $this->image_src_x, $this->image_src_y, $fill);
                            }
                            if ($var_138 < 0) {
                                imagefilledrectangle($tempImage, 0, $this->image_src_y + $var_138, $this->image_src_x, $this->image_src_y, $fill);
                            }
                            if ($var_139 < 0) {
                                imagefilledrectangle($tempImage, 0, 0, -1 * $var_139, $this->image_src_y, $fill);
                            }
                        }
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if ($this->image_resize) {
                        $this->log .= "- resizing...<br />";
                        if ($this->image_ratio_x) {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;calculate x size<br />";
                            $this->$image_dst_x = round($this->image_src_x * $this->image_y / $this->image_src_y);
                            $this->$image_dst_y = $this->image_y;
                        } else {
                            if ($this->image_ratio_y) {
                                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;calculate y size<br />";
                                $this->$image_dst_x = $this->image_x;
                                $this->$image_dst_y = round($this->image_src_y * $this->image_x / $this->image_src_x);
                            } else {
                                if (is_numeric($this->image_ratio_pixels)) {
                                    $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;calculate x/y size to match a number of pixels<br />";
                                    $var_141 = $this->image_src_y * $this->image_src_x;
                                    $var_142 = sqrt($this->image_ratio_pixels / $var_141);
                                    $this->$image_dst_x = round($this->image_src_x * $var_142);
                                    $this->$image_dst_y = round($this->image_src_y * $var_142);
                                } else {
                                    if ($this->image_ratio || $this->image_ratio_crop || $this->image_ratio_fill || $this->image_ratio_no_zoom_in || $this->image_ratio_no_zoom_out) {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;check x/y sizes<br />";
                                        if (!$this->image_ratio_no_zoom_in && !$this->image_ratio_no_zoom_out || $this->image_ratio_no_zoom_in && ($this->image_x < $this->image_src_x || $this->image_y < $this->image_src_y) || $this->image_ratio_no_zoom_out && $this->image_src_x < $this->image_x && $this->image_src_y < $this->image_y) {
                                            $this->$image_dst_x = $this->image_x;
                                            $this->$image_dst_y = $this->image_y;
                                            if ($this->image_ratio_crop) {
                                                if (!is_string($this->image_ratio_crop)) {
                                                    $this->$image_ratio_crop = "";
                                                }
                                                $this->$image_ratio_crop = strtolower($this->image_ratio_crop);
                                                if ($this->image_src_y / $this->image_y < $this->image_src_x / $this->image_x) {
                                                    $this->$image_dst_y = $this->image_y;
                                                    $this->$image_dst_x = intval($this->image_src_x * $this->image_y / $this->image_src_y);
                                                    $cropOffsets = [];
                                                    $cropOffsets["x"] = $this->image_dst_x - $this->image_x;
                                                    if (strpos($this->image_ratio_crop, "l") !== false) {
                                                        $cropOffsets["l"] = 0;
                                                        $cropOffsets["r"] = $cropOffsets["x"];
                                                    } else {
                                                        if (strpos($this->image_ratio_crop, "r") !== false) {
                                                            $cropOffsets["l"] = $cropOffsets["x"];
                                                            $cropOffsets["r"] = 0;
                                                        } else {
                                                            $cropOffsets["l"] = round($cropOffsets["x"] / 2);
                                                            $cropOffsets["r"] = $cropOffsets["x"] - $cropOffsets["l"];
                                                        }
                                                    }
                                                    $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;ratio_crop_x         : " . $cropOffsets["x"] . " (" . $cropOffsets["l"] . ";" . $cropOffsets["r"] . ")<br />";
                                                    if (is_null($this->image_crop)) {
                                                        $this->$image_crop = [0, 0, 0, 0];
                                                    }
                                                } else {
                                                    $this->$image_dst_x = $this->image_x;
                                                    $this->$image_dst_y = intval($this->image_src_y * $this->image_x / $this->image_src_x);
                                                    $cropOffsets = [];
                                                    $cropOffsets["y"] = $this->image_dst_y - $this->image_y;
                                                    if (strpos($this->image_ratio_crop, "t") !== false) {
                                                        $cropOffsets["t"] = 0;
                                                        $cropOffsets["b"] = $cropOffsets["y"];
                                                    } else {
                                                        if (strpos($this->image_ratio_crop, "b") !== false) {
                                                            $cropOffsets["t"] = $cropOffsets["y"];
                                                            $cropOffsets["b"] = 0;
                                                        } else {
                                                            $cropOffsets["t"] = round($cropOffsets["y"] / 2);
                                                            $cropOffsets["b"] = $cropOffsets["y"] - $cropOffsets["t"];
                                                        }
                                                    }
                                                    $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;ratio_crop_y         : " . $cropOffsets["y"] . " (" . $cropOffsets["t"] . ";" . $cropOffsets["b"] . ")<br />";
                                                    if (is_null($this->image_crop)) {
                                                        $this->$image_crop = [0, 0, 0, 0];
                                                    }
                                                }
                                            } else {
                                                if ($this->image_ratio_fill) {
                                                    if (!is_string($this->image_ratio_fill)) {
                                                        $this->$image_ratio_fill = "";
                                                    }
                                                    $this->$image_ratio_fill = strtolower($this->image_ratio_fill);
                                                    if ($this->image_src_x / $this->image_x < $this->image_src_y / $this->image_y) {
                                                        $this->$image_dst_y = $this->image_y;
                                                        $this->$image_dst_x = intval($this->image_src_x * $this->image_y / $this->image_src_y);
                                                        $cropOffsets = [];
                                                        $cropOffsets["x"] = $this->image_dst_x - $this->image_x;
                                                        if (strpos($this->image_ratio_fill, "l") !== false) {
                                                            $cropOffsets["l"] = 0;
                                                            $cropOffsets["r"] = $cropOffsets["x"];
                                                        } else {
                                                            if (strpos($this->image_ratio_fill, "r") !== false) {
                                                                $cropOffsets["l"] = $cropOffsets["x"];
                                                                $cropOffsets["r"] = 0;
                                                            } else {
                                                                $cropOffsets["l"] = round($cropOffsets["x"] / 2);
                                                                $cropOffsets["r"] = $cropOffsets["x"] - $cropOffsets["l"];
                                                            }
                                                        }
                                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;ratio_fill_x         : " . $cropOffsets["x"] . " (" . $cropOffsets["l"] . ";" . $cropOffsets["r"] . ")<br />";
                                                        if (is_null($this->image_crop)) {
                                                            $this->$image_crop = [0, 0, 0, 0];
                                                        }
                                                    } else {
                                                        $this->$image_dst_x = $this->image_x;
                                                        $this->$image_dst_y = intval($this->image_src_y * $this->image_x / $this->image_src_x);
                                                        $cropOffsets = [];
                                                        $cropOffsets["y"] = $this->image_dst_y - $this->image_y;
                                                        if (strpos($this->image_ratio_fill, "t") !== false) {
                                                            $cropOffsets["t"] = 0;
                                                            $cropOffsets["b"] = $cropOffsets["y"];
                                                        } else {
                                                            if (strpos($this->image_ratio_fill, "b") !== false) {
                                                                $cropOffsets["t"] = $cropOffsets["y"];
                                                                $cropOffsets["b"] = 0;
                                                            } else {
                                                                $cropOffsets["t"] = round($cropOffsets["y"] / 2);
                                                                $cropOffsets["b"] = $cropOffsets["y"] - $cropOffsets["t"];
                                                            }
                                                        }
                                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;ratio_fill_y         : " . $cropOffsets["y"] . " (" . $cropOffsets["t"] . ";" . $cropOffsets["b"] . ")<br />";
                                                        if (is_null($this->image_crop)) {
                                                            $this->$image_crop = [0, 0, 0, 0];
                                                        }
                                                    }
                                                } else {
                                                    if ($this->image_src_y / $this->image_y < $this->image_src_x / $this->image_x) {
                                                        $this->$image_dst_x = $this->image_x;
                                                        $this->$image_dst_y = intval($this->image_src_y * $this->image_x / $this->image_src_x);
                                                    } else {
                                                        $this->$image_dst_y = $this->image_y;
                                                        $this->$image_dst_x = intval($this->image_src_x * $this->image_y / $this->image_src_y);
                                                    }
                                                }
                                            }
                                        } else {
                                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;doesn't calculate x/y sizes<br />";
                                            $this->$image_dst_x = $this->image_src_x;
                                            $this->$image_dst_y = $this->image_src_y;
                                        }
                                    } else {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;use plain sizes<br />";
                                        $this->$image_dst_x = $this->image_x;
                                        $this->$image_dst_y = $this->image_y;
                                    }
                                }
                            }
                        }
                        if ($this->image_dst_x < 1) {
                            $this->$image_dst_x = 1;
                        }
                        if ($this->image_dst_y < 1) {
                            $this->$image_dst_y = 1;
                        }
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                        if (2 <= $var_96) {
                            $res = imagecopyresampled($tempImage, $var_131, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_src_x, $this->image_src_y);
                        } else {
                            $res = imagecopyresized($tempImage, $var_131, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_src_x, $this->image_src_y);
                        }
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;resized image object created<br />";
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_src_x y        : " . $this->image_src_x . " x " . $this->image_src_y . "<br />";
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image_dst_x y        : " . $this->image_dst_x . " x " . $this->image_dst_y . "<br />";
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    } else {
                        $this->$image_dst_x = $this->image_src_x;
                        $this->$image_dst_y = $this->image_src_y;
                    }
                    if (!empty($this->image_crop) || !is_null($cropOffsets)) {
                        if (is_array($this->image_crop)) {
                            $vars = $this->image_crop;
                        } else {
                            $vars = explode(" ", $this->image_crop);
                        }
                        if (sizeof($vars) == 4) {
                            list($var_136, $var_137, $var_138, $var_139) = $vars;
                        } else {
                            if (sizeof($vars) == 2) {
                                $var_136 = $vars[0];
                                $var_137 = $vars[1];
                                $var_138 = $vars[0];
                                $var_139 = $vars[1];
                            } else {
                                $var_136 = $vars[0];
                                $var_137 = $vars[0];
                                $var_138 = $vars[0];
                                $var_139 = $vars[0];
                            }
                        }
                        if (0 < strpos($var_136, "%")) {
                            $var_136 = $this->image_dst_y * str_replace("%", "", $var_136) / 100;
                        }
                        if (0 < strpos($var_137, "%")) {
                            $var_137 = $this->image_dst_x * str_replace("%", "", $var_137) / 100;
                        }
                        if (0 < strpos($var_138, "%")) {
                            $var_138 = $this->image_dst_y * str_replace("%", "", $var_138) / 100;
                        }
                        if (0 < strpos($var_139, "%")) {
                            $var_139 = $this->image_dst_x * str_replace("%", "", $var_139) / 100;
                        }
                        if (0 < strpos($var_136, "px")) {
                            $var_136 = str_replace("px", "", $var_136);
                        }
                        if (0 < strpos($var_137, "px")) {
                            $var_137 = str_replace("px", "", $var_137);
                        }
                        if (0 < strpos($var_138, "px")) {
                            $var_138 = str_replace("px", "", $var_138);
                        }
                        if (0 < strpos($var_139, "px")) {
                            $var_139 = str_replace("px", "", $var_139);
                        }
                        $var_136 = (int) $var_136;
                        $var_137 = (int) $var_137;
                        $var_138 = (int) $var_138;
                        $var_139 = (int) $var_139;
                        if (!is_null($cropOffsets)) {
                            if (array_key_exists("t", $cropOffsets)) {
                                $var_136 += $cropOffsets["t"];
                            }
                            if (array_key_exists("r", $cropOffsets)) {
                                $var_137 += $cropOffsets["r"];
                            }
                            if (array_key_exists("b", $cropOffsets)) {
                                $var_138 += $cropOffsets["b"];
                            }
                            if (array_key_exists("l", $cropOffsets)) {
                                $var_139 += $cropOffsets["l"];
                            }
                        }
                        $this->log .= "- crop image : " . $var_136 . " " . $var_137 . " " . $var_138 . " " . $var_139 . " <br />";
                        $this->$image_dst_x = $this->image_dst_x - $var_139 - $var_137;
                        $this->$image_dst_y = $this->image_dst_y - $var_136 - $var_138;
                        if ($this->image_dst_x < 1) {
                            $this->$image_dst_x = 1;
                        }
                        if ($this->image_dst_y < 1) {
                            $this->$image_dst_y = 1;
                        }
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tempImage, $workingImage, 0, 0, $var_139, $var_136, $this->image_dst_x, $this->image_dst_y);
                        if ($var_136 < 0 || $var_137 < 0 || $var_138 < 0 || $var_139 < 0) {
                            if (!empty($this->image_background_color)) {
                                list($var_101, $var_102, $var_103) = $this->function_43($this->image_background_color);
                                $fill = imagecolorallocate($tempImage, $var_101, $var_102, $var_103);
                            } else {
                                $fill = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
                            }
                            if ($var_136 < 0) {
                                imagefilledrectangle($tempImage, 0, 0, $this->image_dst_x, -1 * $var_136, $fill);
                            }
                            if ($var_137 < 0) {
                                imagefilledrectangle($tempImage, $this->image_dst_x + $var_137, 0, $this->image_dst_x, $this->image_dst_y, $fill);
                            }
                            if ($var_138 < 0) {
                                imagefilledrectangle($tempImage, 0, $this->image_dst_y + $var_138, $this->image_dst_x, $this->image_dst_y, $fill);
                            }
                            if ($var_139 < 0) {
                                imagefilledrectangle($tempImage, 0, 0, -1 * $var_139, $this->image_dst_y, $fill);
                            }
                        }
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if (2 <= $var_96 && !empty($this->image_flip)) {
                        $this->$image_flip = strtolower($this->image_flip);
                        $this->log .= "- flip image : " . $this->image_flip . "<br />";
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                        for ($x = 0; $x < $this->image_dst_x; $x++) {
                            for ($y = 0; $y < $this->image_dst_y; $y++) {
                                if (strpos($this->image_flip, "v") !== false) {
                                    imagecopy($tempImage, $workingImage, $this->image_dst_x - $x - 1, $y, $x, $y, 1, 1);
                                } else {
                                    imagecopy($tempImage, $workingImage, $x, $this->image_dst_y - $y - 1, $x, $y, 1, 1);
                                }
                            }
                        }
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if (2 <= $var_96 && is_numeric($this->image_rotate)) {
                        if (!in_array($this->image_rotate, [0, 90, 180, 270])) {
                            $this->$image_rotate = 0;
                        }
                        if ($this->image_rotate != 0) {
                            if ($this->$image_rotate = = 90 || $this->$image_rotate = = 270) {
                                $tempImage = $this->function_44($this->image_dst_y, $this->image_dst_x);
                            } else {
                                $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                            }
                            $this->log .= "- rotate image : " . $this->image_rotate . "<br />";
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                for ($y = 0; $y < $this->image_dst_y; $y++) {
                                    if ($this->$image_rotate = = 90) {
                                        imagecopy($tempImage, $workingImage, $y, $x, $x, $this->image_dst_y - $y - 1, 1, 1);
                                    } else {
                                        if ($this->$image_rotate = = 180) {
                                            imagecopy($tempImage, $workingImage, $x, $y, $this->image_dst_x - $x - 1, $this->image_dst_y - $y - 1, 1, 1);
                                        } else {
                                            if ($this->$image_rotate = = 270) {
                                                imagecopy($tempImage, $workingImage, $y, $x, $this->image_dst_x - $x - 1, $y, 1, 1);
                                            } else {
                                                imagecopy($tempImage, $workingImage, $x, $y, $x, $y, 1, 1);
                                            }
                                        }
                                    }
                                }
                            }
                            if ($this->$image_rotate = = 90 || $this->$image_rotate = = 270) {
                                $var_143 = $this->image_dst_y;
                                $this->$image_dst_y = $this->image_dst_x;
                                $this->$image_dst_x = $var_143;
                            }
                            $workingImage = $this->function_45($tempImage, $workingImage);
                        }
                    }
                    if (2 <= $var_96 && is_numeric($this->image_overlay_percent) && 0 < $this->image_overlay_percent && !empty($this->image_overlay_color)) {
                        $this->log .= "- apply color overlay<br />";
                        list($var_101, $var_102, $var_103) = $this->function_43($this->image_overlay_color);
                        $var_144 = imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        $color = imagecolorallocate($var_144, $var_101, $var_102, $var_103);
                        imagefilledrectangle($var_144, 0, 0, $this->image_dst_x, $this->image_dst_y, $color);
                        $this->function_46($workingImage, $var_144, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_overlay_percent);
                        imagedestroy($var_144);
                    }
                    if (2 <= $var_96 && ($this->image_negative || $this->image_greyscale || is_numeric($this->image_threshold) || is_numeric($this->image_brightness) || is_numeric($this->image_contrast) || !empty($this->image_tint_color))) {
                        $this->log .= "- apply tint, light, contrast correction, negative, greyscale and threshold<br />";
                        if (!empty($this->image_tint_color)) {
                            list($var_145, $var_146, $var_147) = $this->function_43($this->image_tint_color);
                        }
                        imagealphablending($workingImage, true);
                        for ($y = 0; $y < $this->image_dst_y; $y++) {
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                if ($this->image_greyscale) {
                                    $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    $var_100 = $var_149 = $b = round(0 * $var_148["red"] + 0 * $var_148["green"] + 0 * $var_148["blue"]);
                                    $color = imagecolorallocatealpha($workingImage, $var_100, $var_149, $b, $var_148["alpha"]);
                                    imagesetpixel($workingImage, $x, $y, $color);
                                }
                                if (is_numeric($this->image_threshold)) {
                                    $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    $c = round($var_148["red"] + $var_148["green"] + $var_148["blue"]) / 3 - 127;
                                    $var_100 = $var_149 = $b = $this->image_threshold < $c ? 255 : 0;
                                    $color = imagecolorallocatealpha($workingImage, $var_100, $var_149, $b, $var_148["alpha"]);
                                    imagesetpixel($workingImage, $x, $y, $color);
                                }
                                if (is_numeric($this->image_brightness)) {
                                    $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    $var_100 = max(min(round($var_148["red"] + $this->image_brightness * 2), 255), 0);
                                    $var_149 = max(min(round($var_148["green"] + $this->image_brightness * 2), 255), 0);
                                    $b = max(min(round($var_148["blue"] + $this->image_brightness * 2), 255), 0);
                                    $color = imagecolorallocatealpha($workingImage, $var_100, $var_149, $b, $var_148["alpha"]);
                                    imagesetpixel($workingImage, $x, $y, $color);
                                }
                                if (is_numeric($this->image_contrast)) {
                                    $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    $var_100 = max(min(round(($this->image_contrast + 128) * $var_148["red"] / 128), 255), 0);
                                    $var_149 = max(min(round(($this->image_contrast + 128) * $var_148["green"] / 128), 255), 0);
                                    $b = max(min(round(($this->image_contrast + 128) * $var_148["blue"] / 128), 255), 0);
                                    $color = imagecolorallocatealpha($workingImage, $var_100, $var_149, $b, $var_148["alpha"]);
                                    imagesetpixel($workingImage, $x, $y, $color);
                                }
                                if (!empty($this->image_tint_color)) {
                                    $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    $var_100 = min(round($var_145 * $var_148["red"] / 169), 255);
                                    $var_149 = min(round($var_146 * $var_148["green"] / 169), 255);
                                    $b = min(round($var_147 * $var_148["blue"] / 169), 255);
                                    $color = imagecolorallocatealpha($workingImage, $var_100, $var_149, $b, $var_148["alpha"]);
                                    imagesetpixel($workingImage, $x, $y, $color);
                                }
                                if (!empty($this->image_negative)) {
                                    $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    $var_100 = round(255 - $var_148["red"]);
                                    $var_149 = round(255 - $var_148["green"]);
                                    $b = round(255 - $var_148["blue"]);
                                    $color = imagecolorallocatealpha($workingImage, $var_100, $var_149, $b, $var_148["alpha"]);
                                    imagesetpixel($workingImage, $x, $y, $color);
                                }
                            }
                        }
                    }
                    if (2 <= $var_96 && !empty($this->image_border)) {
                        if (is_array($this->image_border)) {
                            $vars = $this->image_border;
                            $this->log .= "- add border : " . implode(" ", $this->image_border) . "<br />";
                        } else {
                            $this->log .= "- add border : " . $this->image_border . "<br />";
                            $vars = explode(" ", $this->image_border);
                        }
                        if (sizeof($vars) == 4) {
                            list($var_136, $var_137, $var_138, $var_139) = $vars;
                        } else {
                            if (sizeof($vars) == 2) {
                                $var_136 = $vars[0];
                                $var_137 = $vars[1];
                                $var_138 = $vars[0];
                                $var_139 = $vars[1];
                            } else {
                                $var_136 = $vars[0];
                                $var_137 = $vars[0];
                                $var_138 = $vars[0];
                                $var_139 = $vars[0];
                            }
                        }
                        if (0 < strpos($var_136, "%")) {
                            $var_136 = $this->image_dst_y * str_replace("%", "", $var_136) / 100;
                        }
                        if (0 < strpos($var_137, "%")) {
                            $var_137 = $this->image_dst_x * str_replace("%", "", $var_137) / 100;
                        }
                        if (0 < strpos($var_138, "%")) {
                            $var_138 = $this->image_dst_y * str_replace("%", "", $var_138) / 100;
                        }
                        if (0 < strpos($var_139, "%")) {
                            $var_139 = $this->image_dst_x * str_replace("%", "", $var_139) / 100;
                        }
                        if (0 < strpos($var_136, "px")) {
                            $var_136 = str_replace("px", "", $var_136);
                        }
                        if (0 < strpos($var_137, "px")) {
                            $var_137 = str_replace("px", "", $var_137);
                        }
                        if (0 < strpos($var_138, "px")) {
                            $var_138 = str_replace("px", "", $var_138);
                        }
                        if (0 < strpos($var_139, "px")) {
                            $var_139 = str_replace("px", "", $var_139);
                        }
                        $var_136 = (int) $var_136;
                        $var_137 = (int) $var_137;
                        $var_138 = (int) $var_138;
                        $var_139 = (int) $var_139;
                        $this->$image_dst_x = $this->image_dst_x + $var_139 + $var_137;
                        $this->$image_dst_y = $this->image_dst_y + $var_136 + $var_138;
                        if (!empty($this->image_border_color)) {
                            list($var_101, $var_102, $var_103) = $this->function_43($this->image_border_color);
                        }
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                        $var_150 = imagecolorallocatealpha($tempImage, $var_101, $var_102, $var_103, 0);
                        imagefilledrectangle($tempImage, 0, 0, $this->image_dst_x, $this->image_dst_y, $var_150);
                        imagecopy($tempImage, $workingImage, $var_139, $var_136, 0, 0, $this->image_dst_x - $var_137 - $var_139, $this->image_dst_y - $var_138 - $var_136);
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if (is_numeric($this->image_frame)) {
                        if (is_array($this->image_frame_colors)) {
                            $vars = $this->image_frame_colors;
                            $this->log .= "- add frame : " . implode(" ", $this->image_frame_colors) . "<br />";
                        } else {
                            $this->log .= "- add frame : " . $this->image_frame_colors . "<br />";
                            $vars = explode(" ", $this->image_frame_colors);
                        }
                        $var_151 = sizeof($vars);
                        $this->$image_dst_x = $this->image_dst_x + $var_151 * 2;
                        $this->$image_dst_y = $this->image_dst_y + $var_151 * 2;
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tempImage, $workingImage, $var_151, $var_151, 0, 0, $this->image_dst_x - $var_151 * 2, $this->image_dst_y - $var_151 * 2);
                        for ($i = 0; $i < $var_151; $i++) {
                            list($var_101, $var_102, $var_103) = $this->function_43($vars[$i]);
                            $c = imagecolorallocate($tempImage, $var_101, $var_102, $var_103);
                            if ($this->$image_frame = = 1) {
                                imageline($tempImage, $i, $i, $this->image_dst_x - $i - 1, $i, $c);
                                imageline($tempImage, $this->image_dst_x - $i - 1, $this->image_dst_y - $i - 1, $this->image_dst_x - $i - 1, $i, $c);
                                imageline($tempImage, $this->image_dst_x - $i - 1, $this->image_dst_y - $i - 1, $i, $this->image_dst_y - $i - 1, $c);
                                imageline($tempImage, $i, $i, $i, $this->image_dst_y - $i - 1, $c);
                            } else {
                                imageline($tempImage, $i, $i, $this->image_dst_x - $i - 1, $i, $c);
                                imageline($tempImage, $this->image_dst_x - $var_151 + $i, $this->image_dst_y - $var_151 + $i, $this->image_dst_x - $var_151 + $i, $var_151 - $i, $c);
                                imageline($tempImage, $this->image_dst_x - $var_151 + $i, $this->image_dst_y - $var_151 + $i, $var_151 - $i, $this->image_dst_y - $var_151 + $i, $c);
                                imageline($tempImage, $i, $i, $i, $this->image_dst_y - $i - 1, $c);
                            }
                        }
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if (0 < $this->image_bevel) {
                        if (empty($this->image_bevel_color1)) {
                            $this->$image_bevel_color1 = "#FFFFFF";
                        }
                        if (empty($this->image_bevel_color2)) {
                            $this->$image_bevel_color2 = "#000000";
                        }
                        list($var_152, $var_153, $var_154) = $this->function_43($this->image_bevel_color1);
                        list($var_155, $var_156, $var_157) = $this->function_43($this->image_bevel_color2);
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tempImage, $workingImage, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y);
                        imagealphablending($tempImage, true);
                        for ($i = 0; $i < $this->image_bevel; $i++) {
                            $var_113 = round($i / $this->image_bevel * 127);
                            $var_158 = imagecolorallocatealpha($tempImage, $var_152, $var_153, $var_154, $var_113);
                            $var_159 = imagecolorallocatealpha($tempImage, $var_155, $var_156, $var_157, $var_113);
                            imageline($tempImage, $i, $i, $this->image_dst_x - $i - 1, $i, $var_158);
                            imageline($tempImage, $this->image_dst_x - $i - 1, $this->image_dst_y - $i, $this->image_dst_x - $i - 1, $i, $var_159);
                            imageline($tempImage, $this->image_dst_x - $i - 1, $this->image_dst_y - $i - 1, $i, $this->image_dst_y - $i - 1, $var_159);
                            imageline($tempImage, $i, $i, $i, $this->image_dst_y - $i - 1, $var_158);
                        }
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if ($this->image_watermark != "" && file_exists($this->image_watermark)) {
                        $this->log .= "- add watermark<br />";
                        $this->$image_watermark_position = strtolower($this->image_watermark_position);
                        $var_160 = getimagesize($this->image_watermark);
                        $var_161 = array_key_exists(2, $var_160) ? $var_160[2] : NULL;
                        $var_162 = false;
                        if ($var_161 == IMAGETYPE_GIF) {
                            if (!function_exists("imagecreatefromgif")) {
                                $this->$error = $this->function_40("watermark_no_create_support", ["GIF"]);
                            } else {
                                $var_144 = @imagecreatefromgif($this->image_watermark);
                                if (!$var_144) {
                                    $this->$error = $this->function_40("watermark_create_error", ["GIF"]);
                                } else {
                                    $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is GIF<br />";
                                    $var_162 = true;
                                }
                            }
                        } else {
                            if ($var_161 == IMAGETYPE_JPEG) {
                                if (!function_exists("imagecreatefromjpeg")) {
                                    $this->$error = $this->function_40("watermark_no_create_support", ["JPEG"]);
                                } else {
                                    $var_144 = @imagecreatefromjpeg($this->image_watermark);
                                    if (!$var_144) {
                                        $this->$error = $this->function_40("watermark_create_error", ["JPEG"]);
                                    } else {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is JPEG<br />";
                                        $var_162 = true;
                                    }
                                }
                            } else {
                                if ($var_161 == IMAGETYPE_PNG) {
                                    if (!function_exists("imagecreatefrompng")) {
                                        $this->$error = $this->function_40("watermark_no_create_support", ["PNG"]);
                                    } else {
                                        $var_144 = @imagecreatefrompng($this->image_watermark);
                                        if (!$var_144) {
                                            $this->$error = $this->function_40("watermark_create_error", ["PNG"]);
                                        } else {
                                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is PNG<br />";
                                            $var_162 = true;
                                        }
                                    }
                                } else {
                                    if ($var_161 == IMAGETYPE_BMP) {
                                        if (!method_exists($this, "imagecreatefrombmp")) {
                                            $this->$error = $this->function_40("watermark_no_create_support", ["BMP"]);
                                        } else {
                                            $var_144 = @$this->function_48($this->image_watermark);
                                            if (!$var_144) {
                                                $this->$error = $this->function_40("watermark_create_error", ["BMP"]);
                                            } else {
                                                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;watermark source image is BMP<br />";
                                                $var_162 = true;
                                            }
                                        }
                                    } else {
                                        $this->$error = $this->function_40("watermark_invalid");
                                    }
                                }
                            }
                        }
                        if ($var_162) {
                            $var_163 = imagesx($var_144);
                            $var_164 = imagesy($var_144);
                            $var_165 = 0;
                            $var_166 = 0;
                            if (is_numeric($this->image_watermark_x)) {
                                if ($this->image_watermark_x < 0) {
                                    $var_165 = $this->image_dst_x - $var_163 + $this->image_watermark_x;
                                } else {
                                    $var_165 = $this->image_watermark_x;
                                }
                            } else {
                                if (strpos($this->image_watermark_position, "r") !== false) {
                                    $var_165 = $this->image_dst_x - $var_163;
                                } else {
                                    if (strpos($this->image_watermark_position, "l") !== false) {
                                        $var_165 = 0;
                                    } else {
                                        $var_165 = ($this->image_dst_x - $var_163) / 2;
                                    }
                                }
                            }
                            if (is_numeric($this->image_watermark_y)) {
                                if ($this->image_watermark_y < 0) {
                                    $var_166 = $this->image_dst_y - $var_164 + $this->image_watermark_y;
                                } else {
                                    $var_166 = $this->image_watermark_y;
                                }
                            } else {
                                if (strpos($this->image_watermark_position, "b") !== false) {
                                    $var_166 = $this->image_dst_y - $var_164;
                                } else {
                                    if (strpos($this->image_watermark_position, "t") !== false) {
                                        $var_166 = 0;
                                    } else {
                                        $var_166 = ($this->image_dst_y - $var_164) / 2;
                                    }
                                }
                            }
                            imagecopyresampled($workingImage, $var_144, $var_165, $var_166, 0, 0, $var_163, $var_164, $var_163, $var_164);
                        } else {
                            $this->$error = $this->function_40("watermark_invalid");
                        }
                    }
                    if (!empty($this->image_text)) {
                        $this->log .= "- add text<br />";
                        $var_167 = $this->file_src_size / 1024;
                        $var_168 = number_format($var_167 / 1024, 1, ".", " ");
                        $var_169 = number_format($var_167, 1, ".", " ");
                        $var_170 = 1024 < $var_167 ? $var_168 . " MB" : $var_169 . " kb";
                        $this->$image_text = str_replace(["[src_name]", "[src_name_body]", "[src_name_ext]", "[src_pathname]", "[src_mime]", "[src_size]", "[src_size_kb]", "[src_size_mb]", "[src_size_human]", "[src_x]", "[src_y]", "[src_pixels]", "[src_type]", "[src_bits]", "[dst_path]", "[dst_name_body]", "[dst_name_ext]", "[dst_name]", "[dst_pathname]", "[dst_x]", "[dst_y]", "[date]", "[time]", "[host]", "[server]", "[ip]", "[gd_version]"], [$this->file_src_name, $this->file_src_name_body, $this->file_src_name_ext, $this->file_src_pathname, $this->file_src_mime, $this->file_src_size, $var_169, $var_168, $var_170, $this->image_src_x, $this->image_src_y, $this->image_src_pixels, $this->image_src_type, $this->image_src_bits, $this->file_dst_path, $this->file_dst_name_body, $this->file_dst_name_ext, $this->file_dst_name, $this->file_dst_pathname, $this->image_dst_x, $this->image_dst_y, date("Y-m-d"), date("H:i:s"), isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "n/a", isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "n/a", isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "n/a", $this->function_39(true)], $this->image_text);
                        if (!is_numeric($this->image_text_padding)) {
                            $this->$image_text_padding = 0;
                        }
                        if (!is_numeric($this->image_text_line_spacing)) {
                            $this->$image_text_line_spacing = 0;
                        }
                        if (!is_numeric($this->image_text_padding_x)) {
                            $this->$image_text_padding_x = $this->image_text_padding;
                        }
                        if (!is_numeric($this->image_text_padding_y)) {
                            $this->$image_text_padding_y = $this->image_text_padding;
                        }
                        $this->$image_text_position = strtolower($this->image_text_position);
                        $this->$image_text_direction = strtolower($this->image_text_direction);
                        $this->$image_text_alignment = strtolower($this->image_text_alignment);
                        if (!is_numeric($this->image_text_font) && 4 < strlen($this->image_text_font) && substr(strtolower($this->image_text_font), -4) == ".gdf") {
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;try to load font " . $this->image_text_font . "... ";
                            if ($this->$image_text_font = @imageloadfont($this->image_text_font)) {
                                $this->log .= "success<br />";
                            } else {
                                $this->log .= "error<br />";
                                $this->$image_text_font = 5;
                            }
                        }
                        $var_171 = explode("\n", $this->image_text);
                        $var_172 = imagefontwidth($this->image_text_font);
                        $var_173 = imagefontheight($this->image_text_font);
                        $var_174 = 0;
                        $var_175 = 0;
                        $var_176 = 0;
                        $var_177 = 0;
                        foreach ($var_171 as $k => $var_124) {
                            if ($this->$image_text_direction = = "v") {
                                $h = $var_172 * strlen($var_124);
                                if ($var_174 < $h) {
                                    $var_174 = $h;
                                }
                                $var_177 = $var_173;
                                $var_175 += $var_177 + ($k < sizeof($var_171) - 1 ? $this->image_text_line_spacing : 0);
                            } else {
                                $var_178 = $var_172 * strlen($var_124);
                                if ($var_175 < $var_178) {
                                    $var_175 = $var_178;
                                }
                                $var_176 = $var_173;
                                $var_174 += $var_176 + ($k < sizeof($var_171) - 1 ? $this->image_text_line_spacing : 0);
                            }
                        }
                        $var_175 += 2 * $this->image_text_padding_x;
                        $var_174 += 2 * $this->image_text_padding_y;
                        $var_179 = 0;
                        $var_180 = 0;
                        if (is_numeric($this->image_text_x)) {
                            if ($this->image_text_x < 0) {
                                $var_179 = $this->image_dst_x - $var_175 + $this->image_text_x;
                            } else {
                                $var_179 = $this->image_text_x;
                            }
                        } else {
                            if (strpos($this->image_text_position, "r") !== false) {
                                $var_179 = $this->image_dst_x - $var_175;
                            } else {
                                if (strpos($this->image_text_position, "l") !== false) {
                                    $var_179 = 0;
                                } else {
                                    $var_179 = ($this->image_dst_x - $var_175) / 2;
                                }
                            }
                        }
                        if (is_numeric($this->image_text_y)) {
                            if ($this->image_text_y < 0) {
                                $var_180 = $this->image_dst_y - $var_174 + $this->image_text_y;
                            } else {
                                $var_180 = $this->image_text_y;
                            }
                        } else {
                            if (strpos($this->image_text_position, "b") !== false) {
                                $var_180 = $this->image_dst_y - $var_174;
                            } else {
                                if (strpos($this->image_text_position, "t") !== false) {
                                    $var_180 = 0;
                                } else {
                                    $var_180 = ($this->image_dst_y - $var_174) / 2;
                                }
                            }
                        }
                        if (!empty($this->image_text_background)) {
                            list($var_101, $var_102, $var_103) = $this->function_43($this->image_text_background);
                            if (2 <= $var_96 && is_numeric($this->image_text_background_percent) && 0 <= $this->image_text_background_percent && $this->image_text_background_percent <= 100) {
                                $var_144 = imagecreatetruecolor($var_175, $var_174);
                                $var_105 = imagecolorallocate($var_144, $var_101, $var_102, $var_103);
                                imagefilledrectangle($var_144, 0, 0, $var_175, $var_174, $var_105);
                                $this->function_46($workingImage, $var_144, $var_179, $var_180, 0, 0, $var_175, $var_174, $this->image_text_background_percent);
                                imagedestroy($var_144);
                            } else {
                                $var_105 = imagecolorallocate($workingImage, $var_101, $var_102, $var_103);
                                imagefilledrectangle($workingImage, $var_179, $var_180, $var_179 + $var_175, $var_180 + $var_174, $var_105);
                            }
                        }
                        $var_179 += $this->image_text_padding_x;
                        $var_180 += $this->image_text_padding_y;
                        $var_181 = $var_175 - 2 * $this->image_text_padding_x;
                        $var_182 = $var_174 - 2 * $this->image_text_padding_y;
                        list($var_101, $var_102, $var_103) = $this->function_43($this->image_text_color);
                        if (2 <= $var_96 && is_numeric($this->image_text_percent) && 0 <= $this->image_text_percent && $this->image_text_percent <= 100) {
                            if ($var_181 < 0) {
                                $var_181 = 0;
                            }
                            if ($var_182 < 0) {
                                $var_182 = 0;
                            }
                            $var_144 = $this->function_44($var_181, $var_182, false, true);
                            $var_183 = imagecolorallocate($var_144, $var_101, $var_102, $var_103);
                            foreach ($var_171 as $k => $var_124) {
                                if ($this->$image_text_direction = = "v") {
                                    imagestringup($var_144, $this->image_text_font, $k * ($var_177 + (0 < $k && $k < sizeof($var_171) ? $this->image_text_line_spacing : 0)), $var_174 - 2 * $this->image_text_padding_y - ($this->$image_text_alignment = = "l" ? 0 : ($var_182 - strlen($var_124) * $var_172) / ($this->$image_text_alignment = = "r" ? 1 : 2)), $var_124, $var_183);
                                } else {
                                    imagestring($var_144, $this->image_text_font, $this->$image_text_alignment = = "l" ? 0 : ($var_181 - strlen($var_124) * $var_172) / ($this->$image_text_alignment = = "r" ? 1 : 2), $k * ($var_176 + (0 < $k && $k < sizeof($var_171) ? $this->image_text_line_spacing : 0)), $var_124, $var_183);
                                }
                            }
                            $this->function_46($workingImage, $var_144, $var_179, $var_180, 0, 0, $var_181, $var_182, $this->image_text_percent);
                            imagedestroy($var_144);
                        } else {
                            $var_183 = var_184($workingImage, $var_101, $var_102, $var_103);
                            foreach ($var_171 as $k => $var_124) {
                                if ($this->$image_text_direction = = "v") {
                                    imagestringup($workingImage, $this->image_text_font, $var_179 + $k * ($var_177 + (0 < $k && $k < sizeof($var_171) ? $this->image_text_line_spacing : 0)), $var_180 + $var_174 - 2 * $this->image_text_padding_y - ($this->$image_text_alignment = = "l" ? 0 : ($var_182 - strlen($var_124) * $var_172) / ($this->$image_text_alignment = = "r" ? 1 : 2)), $var_124, $var_183);
                                } else {
                                    imagestring($workingImage, $this->image_text_font, $var_179 + ($this->$image_text_alignment = = "l" ? 0 : ($var_181 - strlen($var_124) * $var_172) / ($this->$image_text_alignment = = "r" ? 1 : 2)), $var_180 + $k * ($var_176 + (0 < $k && $k < sizeof($var_171) ? $this->image_text_line_spacing : 0)), $var_124, $var_183);
                                }
                            }
                        }
                    }
                    if ($this->image_reflection_height) {
                        $this->log .= "- add reflection : " . $this->image_reflection_height . "<br />";
                        $image_reflection_height = $this->image_reflection_height;
                        if (0 < strpos($image_reflection_height, "%")) {
                            $image_reflection_height = $this->image_dst_y * str_replace("%", "", $image_reflection_height / 100);
                        }
                        if (0 < strpos($image_reflection_height, "px")) {
                            $image_reflection_height = str_replace("px", "", $image_reflection_height);
                        }
                        $image_reflection_height = (int) $image_reflection_height;
                        if ($this->image_dst_y < $image_reflection_height) {
                            $image_reflection_height = $this->image_dst_y;
                        }
                        if (empty($this->image_reflection_opacity)) {
                            $this->$image_reflection_opacity = 60;
                        }
                        $tempImage = $this->function_44($this->image_dst_x, $this->image_dst_y + $image_reflection_height + $this->image_reflection_space, true);
                        $var_185 = $this->image_reflection_opacity;
                        imagecopy($tempImage, $workingImage, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y + ($this->image_reflection_space < 0 ? $this->image_reflection_space : 0));
                        if (0 < $image_reflection_height + $this->image_reflection_space) {
                            if (!empty($this->image_background_color)) {
                                list($var_101, $var_102, $var_103) = $this->function_43($this->image_background_color);
                                $fill = imagecolorallocate($tempImage, $var_101, $var_102, $var_103);
                            } else {
                                $fill = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
                            }
                            imagefill($tempImage, round($this->image_dst_x / 2), $this->image_dst_y + $image_reflection_height + $this->image_reflection_space - 1, $fill);
                        }
                        for ($y = 0; $y < $image_reflection_height; $y++) {
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                $var_186 = imagecolorsforindex($tempImage, imagecolorat($tempImage, $x, $y + $this->image_dst_y + $this->image_reflection_space));
                                $var_187 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $this->image_dst_y - $y - 1 + ($this->image_reflection_space < 0 ? $this->image_reflection_space : 0)));
                                $var_188 = 1 - $var_187["alpha"] / 127;
                                $var_189 = 1 - $var_186["alpha"] / 127;
                                $var_112 = $var_188 * $var_185 / 100;
                                if (0 < $var_112) {
                                    $var_101 = round(($var_187["red"] * $var_112 + $var_186["red"] * $var_189) / ($var_189 + $var_112));
                                    $var_102 = round(($var_187["green"] * $var_112 + $var_186["green"] * $var_189) / ($var_189 + $var_112));
                                    $var_103 = round(($var_187["blue"] * $var_112 + $var_186["blue"] * $var_189) / ($var_189 + $var_112));
                                    $var_113 = $var_112 + $var_189;
                                    if (1 < $var_113) {
                                        $var_113 = 1;
                                    }
                                    $var_113 = round((1 - $var_113) * 127);
                                    $color = imagecolorallocatealpha($tempImage, $var_101, $var_102, $var_103, $var_113);
                                    imagesetpixel($tempImage, $x, $y + $this->image_dst_y + $this->image_reflection_space, $color);
                                }
                            }
                            if (0 < $var_185) {
                                $var_185 = $var_185 - $this->image_reflection_opacity / $image_reflection_height;
                            }
                        }
                        $this->$image_dst_y = $this->image_dst_y + $image_reflection_height + $this->image_reflection_space;
                        $workingImage = $this->function_45($tempImage, $workingImage);
                    }
                    if (is_numeric($this->jpeg_size) && 0 < $this->jpeg_size && ($this->$image_convert = = "jpeg" || $this->$image_convert = = "jpg")) {
                        $this->log .= "- JPEG desired file size : " . $this->jpeg_size . "<br />";
                        ob_start();
                        imagejpeg($workingImage, "", 75);
                        $var_190 = ob_get_contents();
                        ob_end_clean();
                        $var_191 = strlen($var_190);
                        ob_start();
                        imagejpeg($workingImage, "", 50);
                        $var_190 = ob_get_contents();
                        ob_end_clean();
                        $var_192 = strlen($var_190);
                        ob_start();
                        imagejpeg($workingImage, "", 25);
                        $var_190 = ob_get_contents();
                        ob_end_clean();
                        $var_193 = strlen($var_190);
                        $var_194 = 25 / ($var_192 - $var_193);
                        $var_195 = 25 / ($var_191 - $var_192);
                        $var_196 = 50 / ($var_191 - $var_193);
                        $var_197 = ($var_194 + $var_195 + $var_196) / 3;
                        $var_198 = round($var_197 * ($this->jpeg_size - $var_192) + 50);
                        if ($var_198 < 1) {
                            $this->$jpeg_quality = 1;
                        } else {
                            if (100 < $var_198) {
                                $this->$jpeg_quality = 100;
                            } else {
                                $this->$jpeg_quality = $var_198;
                            }
                        }
                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;JPEG quality factor set to " . $this->jpeg_quality . "<br />";
                    }
                    $this->log .= "- converting...<br />";
                    switch ($this->image_convert) {
                        case "gif":
                            if (imageistruecolor($workingImage)) {
                                $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;true color to palette<br />";
                                $var_199 = [[]];
                                for ($x = 0; $x < $this->image_dst_x; $x++) {
                                    for ($y = 0; $y < $this->image_dst_y; $y++) {
                                        $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                        $var_199[$x][$y] = $var_148["alpha"];
                                    }
                                }
                                list($var_101, $var_102, $var_103) = $this->function_43($this->image_default_color);
                                for ($x = 0; $x < $this->image_dst_x; $x++) {
                                    for ($y = 0; $y < $this->image_dst_y; $y++) {
                                        if (0 < $var_199[$x][$y]) {
                                            $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                            $var_113 = $var_199[$x][$y] / 127;
                                            $var_148["red"] = round($var_148["red"] * (1 - $var_113) + $var_101 * $var_113);
                                            $var_148["green"] = round($var_148["green"] * (1 - $var_113) + $var_102 * $var_113);
                                            $var_148["blue"] = round($var_148["blue"] * (1 - $var_113) + $var_103 * $var_113);
                                            $color = imagecolorallocate($workingImage, $var_148["red"], $var_148["green"], $var_148["blue"]);
                                            imagesetpixel($workingImage, $x, $y, $color);
                                        }
                                    }
                                }
                                if (empty($this->image_background_color)) {
                                    imagetruecolortopalette($workingImage, true, 255);
                                    $var_185 = imagecolorallocate($workingImage, 254, 1, 253);
                                    imagecolortransparent($workingImage, $var_185);
                                    for ($x = 0; $x < $this->image_dst_x; $x++) {
                                        for ($y = 0; $y < $this->image_dst_y; $y++) {
                                            if (120 < $var_199[$x][$y]) {
                                                imagesetpixel($workingImage, $x, $y, $var_185);
                                            }
                                        }
                                    }
                                }
                                unset($var_199);
                            }
                            break;
                        case "jpg":
                        case "bmp":
                            $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;fills in transparency with default color<br />";
                            list($var_101, $var_102, $var_103) = $this->function_43($this->image_default_color);
                            $var_185 = imagecolorallocate($workingImage, $var_101, $var_102, $var_103);
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                for ($y = 0; $y < $this->image_dst_y; $y++) {
                                    if (imageistruecolor($workingImage)) {
                                        $var_200 = imagecolorat($workingImage, $x, $y);
                                        $var_148 = ["red" => $var_200 >> 16 & 255, "green" => $var_200 >> 8 & 255, "blue" => $var_200 & 255, "alpha" => ($var_200 & 2130706432) >> 24];
                                    } else {
                                        $var_148 = imagecolorsforindex($workingImage, imagecolorat($workingImage, $x, $y));
                                    }
                                    if ($var_148["alpha"] == 127) {
                                        imagesetpixel($workingImage, $x, $y, $var_185);
                                    } else {
                                        if (0 < $var_148["alpha"]) {
                                            $var_113 = $var_148["alpha"] / 127;
                                            $var_148["red"] = round($var_148["red"] * (1 - $var_113) + $var_101 * $var_113);
                                            $var_148["green"] = round($var_148["green"] * (1 - $var_113) + $var_102 * $var_113);
                                            $var_148["blue"] = round($var_148["blue"] * (1 - $var_113) + $var_103 * $var_113);
                                            $color = imagecolorclosest($workingImage, $var_148["red"], $var_148["green"], $var_148["blue"]);
                                            imagesetpixel($workingImage, $x, $y, $color);
                                        }
                                    }
                                }
                            }
                            break;
                        default:
                            $this->log .= "- saving image...<br />";
                            switch ($this->image_convert) {
                                case "jpeg":
                                case "jpg":
                                    if (!$var_120) {
                                        $result = @imagejpeg($workingImage, $this->file_dst_pathname, $this->jpeg_quality);
                                    } else {
                                        ob_start();
                                        $result = @imagejpeg($workingImage, "", $this->jpeg_quality);
                                        $var_121 = ob_get_contents();
                                        ob_end_clean();
                                    }
                                    if (!$result) {
                                        $this->$processed = false;
                                        $this->$error = $this->function_40("file_create", ["JPEG"]);
                                    } else {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;JPEG image created<br />";
                                    }
                                    break;
                                case "png":
                                    imagealphablending($workingImage, false);
                                    imagesavealpha($workingImage, true);
                                    if (!$var_120) {
                                        $result = @imagepng($workingImage, $this->file_dst_pathname);
                                    } else {
                                        ob_start();
                                        $result = @imagepng($workingImage);
                                        $var_121 = ob_get_contents();
                                        ob_end_clean();
                                    }
                                    if (!$result) {
                                        $this->$processed = false;
                                        $this->$error = $this->function_40("file_create", ["PNG"]);
                                    } else {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;PNG image created<br />";
                                    }
                                    break;
                                case "gif":
                                    if (!$var_120) {
                                        $result = @imagegif($workingImage, $this->file_dst_pathname);
                                    } else {
                                        ob_start();
                                        $result = @imagegif($workingImage);
                                        $var_121 = ob_get_contents();
                                        ob_end_clean();
                                    }
                                    if (!$result) {
                                        $this->$processed = false;
                                        $this->$error = $this->function_40("file_create", ["GIF"]);
                                    } else {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;GIF image created<br />";
                                    }
                                    break;
                                case "bmp":
                                    if (!$var_120) {
                                        $result = $this->function_49($workingImage, $this->file_dst_pathname);
                                    } else {
                                        ob_start();
                                        $result = $this->function_49($workingImage);
                                        $var_121 = ob_get_contents();
                                        ob_end_clean();
                                    }
                                    if (!$result) {
                                        $this->$processed = false;
                                        $this->$error = $this->function_40("file_create", ["BMP"]);
                                    } else {
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;BMP image created<br />";
                                    }
                                    break;
                                default:
                                    $this->$processed = false;
                                    $this->$error = $this->function_40("no_conversion_type");
                                    if ($this->processed) {
                                        if (is_resource($var_131)) {
                                            imagedestroy($var_131);
                                        }
                                        if (is_resource($workingImage)) {
                                            imagedestroy($workingImage);
                                        }
                                        $this->log .= "&nbsp;&nbsp;&nbsp;&nbsp;image objects destroyed<br />";
                                    }
                            }
                    }
                }
            } else {
                $this->log .= "- no image processing wanted<br />";
                if (!$var_120) {
                    if (!copy($this->file_src_pathname, $this->file_dst_pathname)) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("copy_failed");
                    }
                } else {
                    $var_121 = @file_get_contents($this->file_src_pathname);
                    if ($var_121 === false) {
                        $this->$processed = false;
                        $this->$error = $this->function_40("reading_failed");
                    }
                }
            }
        }
        if ($this->processed) {
            $this->log .= "- <b>process OK</b><br />";
        } else {
            $this->log .= "- <b>error</b>: " . $this->error . "<br />";
        }
        $this->function_38();
        if ($var_120) {
            return $var_121;
        }
    }
    public function function_50()
    {
        $this->log .= "<b>cleanup</b><br />";
        $this->log .= "- delete temp file " . $this->file_src_pathname . "<br />";
        @unlink($this->file_src_pathname);
    }
    public function function_48($filename)
    {
        if (!($var_201 = fopen($filename, "rb"))) {
            return false;
        }
        $file = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($var_201, 14));
        if ($file["file_type"] != 19778) {
            return false;
        }
        $var_202 = unpack("Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel/Vcompression/Vsize_bitmap/Vhoriz_resolution/Vvert_resolution/Vcolors_used/Vcolors_important", fread($var_201, 40));
        $var_202["colors"] = pow(2, $var_202["bits_per_pixel"]);
        if ($var_202["size_bitmap"] == 0) {
            $var_202["size_bitmap"] = $file["file_size"] - $file["bitmap_offset"];
        }
        $var_202["bytes_per_pixel"] = $var_202["bits_per_pixel"] / 8;
        $var_202["bytes_per_pixel2"] = ceil($var_202["bytes_per_pixel"]);
        $var_202["decal"] = $var_202["width"] * $var_202["bytes_per_pixel"] / 4;
        $var_202["decal"] -= floor($var_202["width"] * $var_202["bytes_per_pixel"] / 4);
        $var_202["decal"] = 4 - 4 * $var_202["decal"];
        if ($var_202["decal"] == 4) {
            $var_202["decal"] = 0;
        }
        $var_203 = [];
        if ($var_202["colors"] < 16777216) {
            $var_203 = unpack("V" . $var_202["colors"], fread($var_201, $var_202["colors"] * 4));
        }
        $var_204 = fread($var_201, $var_202["size_bitmap"]);
        $var_205 = chr(0);
        $res = imagecreatetruecolor($var_202["width"], $var_202["height"]);
        $var_206 = 0;
        $var_207 = $var_202["height"] - 1;
        while (0 <= $var_207) {
            $var_208 = 0;
            while ($var_208 < $var_202["width"]) {
                if ($var_202["bits_per_pixel"] == 24) {
                    $color = unpack("V", substr($var_204, $var_206, 3) . $var_205);
                } else {
                    if ($var_202["bits_per_pixel"] == 16) {
                        $color = unpack("n", substr($var_204, $var_206, 2));
                        $color[1] = $var_203[$color[1] + 1];
                    } else {
                        if ($var_202["bits_per_pixel"] == 8) {
                            $color = unpack("n", $var_205 . substr($var_204, $var_206, 1));
                            $color[1] = $var_203[$color[1] + 1];
                        } else {
                            if ($var_202["bits_per_pixel"] == 4) {
                                $color = unpack("n", $var_205 . substr($var_204, floor($var_206), 1));
                                if ($var_206 * 2 % 2 == 0) {
                                    $color[1] = $color[1] >> 4;
                                } else {
                                    $color[1] = $color[1] & 15;
                                }
                                $color[1] = $var_203[$color[1] + 1];
                            } else {
                                if ($var_202["bits_per_pixel"] == 1) {
                                    $color = unpack("n", $var_205 . substr($var_204, floor($var_206), 1));
                                    if ($var_206 * 8 % 8 == 0) {
                                        $color[1] = $color[1] >> 7;
                                    } else {
                                        if ($var_206 * 8 % 8 == 1) {
                                            $color[1] = ($color[1] & 64) >> 6;
                                        } else {
                                            if ($var_206 * 8 % 8 == 2) {
                                                $color[1] = ($color[1] & 32) >> 5;
                                            } else {
                                                if ($var_206 * 8 % 8 == 3) {
                                                    $color[1] = ($color[1] & 16) >> 4;
                                                } else {
                                                    if ($var_206 * 8 % 8 == 4) {
                                                        $color[1] = ($color[1] & 8) >> 3;
                                                    } else {
                                                        if ($var_206 * 8 % 8 == 5) {
                                                            $color[1] = ($color[1] & 4) >> 2;
                                                        } else {
                                                            if ($var_206 * 8 % 8 == 6) {
                                                                $color[1] = ($color[1] & 2) >> 1;
                                                            } else {
                                                                if ($var_206 * 8 % 8 == 7) {
                                                                    $color[1] = $color[1] & 1;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $color[1] = $var_203[$color[1] + 1];
                                } else {
                                    return false;
                                }
                            }
                        }
                    }
                }
                imagesetpixel($res, $var_208, $var_207, $color[1]);
                $var_208++;
                $var_206 += $var_202["bytes_per_pixel"];
            }
            $var_207--;
            $var_206 += $var_202["decal"];
        }
        fclose($var_201);
        return $res;
    }
    public function function_49(&$im, $filename = "")
    {
        if (!$im) {
            return false;
        }
        $var_178 = imagesx($im);
        $h = imagesy($im);
        $result = "";
        if (!imageistruecolor($im)) {
            $tempImage = imagecreatetruecolor($var_178, $h);
            imagecopy($tempImage, $im, 0, 0, 0, 0, $var_178, $h);
            imagedestroy($im);
            $im =& $tempImage;
        }
        $var_209 = $var_178 * 3;
        $var_210 = $var_209 + 3 & -4;
        $var_211 = $var_210 * $h;
        $var_212 = 54;
        $var_213 = $var_212 + $var_211;
        $result .= substr("BM", 0, 2);
        $result .= pack("VvvV", $var_213, 0, 0, $var_212);
        $result .= pack("VVVvvVVVVVV", 40, $var_178, $h, 1, 24, 0, $var_211, 0, 0, 0, 0);
        $var_214 = $var_210 - $var_209;
        $y = $h - 1;
        while (0 <= $y) {
            for ($x = 0; $x < $var_178; $x++) {
                $var_215 = imagecolorat($im, $x, $y);
                $result .= substr(pack("V", $var_215), 0, 3);
            }
            for ($i = 0; $i < $var_214; $i++) {
                $result .= pack("C", 0);
            }
            --$y;
        }
        if ($filename == "") {
            echo $result;
        } else {
            $file = fopen($filename, "wb");
            fwrite($file, $result);
            fclose($file);
        }
        return true;
    }
}

?>