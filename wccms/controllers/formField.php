<?php
class FormField
{
   protected $rowformfield;
   protected $rowfield;
   protected $ContentValue;
   protected $required;
   protected $itemclass;
   protected $itemclassname;
   protected $infomark;
   protected $TypeDebug;
   protected $PageType;
   protected $maxGalleryImages = 10;

   public function __construct($rowformfield, $rowfield, $ContentValue, $TypeDebug , $PageType, $infomark = '')
   {
      $this->rowformfield = $rowformfield;
      $this->rowfield = $rowfield;
      $this->ContentValue = $ContentValue;
      $this->infomark = $infomark;

      if ($rowformfield['maxgalleryimages'] > 0) {
         $this->maxGalleryImages = $rowformfield['maxgalleryimages'];
      }else {
         $this->maxGalleryImages = 10;
      }

      // SET REQUIRED
      if ($rowformfield["required"] == 'Yes') {
         $this->required = 'required';
      } else {
         $this->required = '';
      }
      // SET WIDTH STYLE
      switch ($rowformfield["class"]) {
         case 'small':
            $this->itemclass = 'width:25%';
            $this->itemclassname = 'colsmall';
            break;
         case 'm-25':
            $this->itemclass = 'width:25%';
            $this->itemclassname = 'colsmall';
            break;
         case 'medium':
            $this->itemclass = 'width:50%';
            $this->itemclassname = 'colmedium';
            break;
         case 'm-50':
            $this->itemclass = 'width:50%';
            $this->itemclassname = 'colmedium';
            break;
         default:
            $this->itemclass = 'width:50%';
            $this->itemclassname = 'colmedium';
            break;
      }
      $this->TypeDebug = $TypeDebug;
      $this->PageType = $PageType;
   }

   /**
    * Render form field
    *
    * @param int $recordnumber  Record number
    * @param int $formnumber  Form number
    * @param bool $gallery  Gallery
    * @param string $baseURL  Base URL
    * @return string  Form field HTML
    */
   public function render($recordnumber = 0, $formnumber = 0, $gallery = false, $baseURL = '')
   {
      switch ($this->rowfield['id']) {
         case 1:
            return $this->renderTextField();
         case 2:
            return $this->renderPasswordField();
         case 3:
            return $this->renderRadioField();
         case 4:
            return $this->renderCheckboxField();
         case 5:
            return $this->renderColourPickerField();
         case 6:
            return $this->renderBootstrapDateField(); // NOT WORKING RIGHT NOW 
         case 7:
            return $this->renderEmailField();
         case 8:
            return $this->renderInputField();
         case 9:
            return $this->renderNumberField();
         case 10:
            return $this->renderRangeField();
         case 11:
            return $this->renderSearchField();
         case 12:
            return $this->renderTelField();
         case 13:
            return $this->renderTimeField();
         case 14:
            return $this->renderInputField();
         case 15:
            return $this->renderInputField();
         case 16:
            return $this->renderSelectField();
         case 17:
            return $this->renderRadioField(true);
         case 18:
            return $this->renderSelectFromTableField($recordnumber, $formnumber, $gallery);
         case 19:
            return $this->renderTextAreaTinymceField();
         case 20:
            return $this->renderTextAreaField();
         case 21:
            return $this->renderFileUploadField($recordnumber, $formnumber, $gallery);
         case 22:
            return $this->renderDateField();
         case 23:
            return $this->renderGalleryField($recordnumber, $formnumber);
            // Add more cases for other field types as needed
         case 24:
            // return $this->renderDownloadCV($baseURL);
         case 25:
            //return $this->renderDownloadCV($baseURL);
         case 26:
            //return $this->renderDownloadCV($baseURL);
         case 27:
            //return $this->renderDownloadCV($baseURL);
         case 28:
            return $this->renderDateTimeField();
         case 29:
            return $this->renderBSDateTimeField();
         case 30:
            return $this->renderBSDateField();
         case 31:
            return $this->renderBSTimeField();
         default:
            return '';
      }
   }

   private function escape_html($text)
   {
      return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
   }



   // V4 version
/*
   protected function renderTextField()
   {
      $ContentValue = stripslashes($this->ContentValue);
      $ContentValue = $this->escape_html($ContentValue);

      $read = "readonly" ;
      $readsymbol = "" ;
      if ($this->rowformfield["allowedit"] == 'Yes'  OR $this->PageType == 'Add')
      {
         $read = "" ;
         $readsymbol = "<span style='color:red;'>*</span> ";
      }

         $output = "<div class='form-group cmsform {$this->rowformfield["label"]}' style='{$this->itemclass}'>";
         $output .= "<label for='exampleInputEmail1'>$readsymbol {$this->rowformfield["label"]} - {$this->PageType}";

         if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark} <i class='fas fa-info-circle'></i></a>";
         }
         if ($this->TypeDebug == 'Yes') { // Debug Mode
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]}) - ['{$this->rowformfield["name"]}'] - $this->PageType Y</span>";
         }
         
         $output .= "</label>";

         $output .= "<input type='{$this->rowfield["type"]}' style='font-weight:400;' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' name='{$this->rowformfield["name"]}' placeholder='{$this->rowformfield["placeholder"]}' value='{$ContentValue}' {$this->required} $read>";
         $output .= "<br><span style='font-weight:700;'>{$this->rowformfield["comment"]}<span><br>";
         $output .= "</div>";

      return $output;
   }
*/
   //V3 version
  
   protected function renderTextField()
   {
      $ContentValue = stripslashes($this->ContentValue);
      $ContentValue = $this->escape_html($ContentValue);

      if ($this->rowformfield["allowedit"] == 'Yes') {
         $output = "<div class='form-group cmsform {$this->rowformfield["label"]} {$this->itemclassname}' style='padding-bottom:15px;'>";
        // $output = "<div class='form-group cmsform {$this->rowformfield["label"]}' style='{$this->itemclass};padding-bottom:15px;'>";
         $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

         if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

            if ($this->TypeDebug == 'Yes') { // Debug Mode
               $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
         }

         $output .= "</label>";
         $output .= "<input type='{$this->rowfield["type"]}' style='font-weight:400;' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' name='{$this->rowformfield["name"]}' placeholder='{$this->rowformfield["placeholder"]}' value='{$ContentValue}' {$this->required}>";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
         $output .= "</div>";
      } 
      else 
      {
         $output = "<h6 style='padding-bottom:15px;'>{$this->rowformfield["label"]}<br><strong>{$ContentValue}</strong></h6>";
      }

      return $output;
   }
 /**/
   protected function renderNumberField()
   {
      if ($this->rowformfield["max"] == 0) {
         $max = '';
      } else {
         $max = $this->rowformfield["max"];
      }

      $output = "<div class='form-group cmsform  {$this->itemclassname}' style=''>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label>";
      $output .= "<input type='" . $this->rowfield["type"] . "' class='form-control' id='exampleInputEmail1' name='" . $this->rowformfield["name"] . "' placeholder='" . $this->rowformfield["placeholder"] . "' min='" . $this->rowformfield["min"] . "' max='" . $max . "'  step='" . $this->rowformfield["step"] . "' value='" . $this->ContentValue . "' $this->required style=''>";
      $output .= "<span>" . $this->rowformfield["comment"] . "<span>";
      $output .= "</div>";

      return $output;
   }

   protected function renderEmailField()
   {
      $output = "<div class='form-group cmsform  {$this->itemclassname}' style=''>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label>";
      $output .= "<input type='email' name='{$this->rowformfield["name"]}' style='font-weight:400;' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' value='{$this->ContentValue}' {$this->required}>";
      $output .= "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";

      return $output;
   }

   protected function renderInputField()
   {
      $output = "<div class='form-group cmsform  {$this->itemclassname}' style=''>";
      $output .= "<label for='{$this->rowformfield["name"]}'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label><br>";
      $output .= "<input type='{$this->rowfield["type"]}' name='{$this->rowformfield["name"]}' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' value='{$this->ContentValue}' placeholder='{$this->rowformfield["placeholder"]}' {$this->required}>";
      $output .= "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";

      return $output;
   }

   protected function renderTelField()
   {
      if ($this->rowformfield["allowedit"] == 'Yes') {
         $output = "<div class='form-group cmsform  {$this->itemclassname}' style=''>";
         $output .= "<label for='{$this->rowformfield["name"]}'>{$this->rowformfield["label"]}";

         if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
            if ($this->TypeDebug == 'Yes') {
               $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
         }
         $output .= "</label><br>";
         $output .= "<input type='{$this->rowfield["type"]}' style='font-weight:400;' name='{$this->rowformfield["name"]}' class='form-control' id='exampleInputEmail1' {$this->required} value='{$this->ContentValue}' placeholder='{$this->rowformfield["placeholder"]}'>";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
         $output .= "</div>";
      } else {
         $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
      }

      return $output;
   }

   protected function renderRangeField()
   {
      if ($this->rowformfield["max"] == 0) {
         $max = '100';
      } else {
         $max = $this->rowformfield["max"];
      }
      $output = "<div class='form-group cmsform  {$this->itemclassname}' style=''>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label>";
      $output .= "<input type='{$this->rowfield["type"]}' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' name='{$this->rowformfield["name"]}' placeholder='{$this->rowformfield["placeholder"]}' min='{$this->rowformfield["min"]}' max='" . $max . "' step='{$this->rowformfield["step"]}' value='{$this->ContentValue}'>";
      echo "<span>{$this->rowformfield["comment"]}<span>";
      echo "</div>";

      return $output;
   }

   protected function renderPasswordField()
   {
      $output = "<div class='form-group cmsform  {$this->itemclassname}' style=''>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

         if ($this->TypeDebug == 'Yes') {
            echo "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }

      $output .= "</label>";
      $output .= "<input type='{$this->rowfield["type"]}' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' name='{$this->rowformfield["name"]}' placeholder='{$this->rowformfield["placeholder"]}' value='{$this->ContentValue}' {$this->required}>";
      $output .= "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";

      return $output;
   }

   /**
    * Get radio options
    *
    * @return array|null
    */
   private function getRadioOptions()
   {
      $query = "SELECT * FROM `cms_form_field_options` 
      WHERE `form_field` = '" . $this->rowformfield['id'] . "'";
      $result = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if ($result) {
         return $result;
      } else {
         return null;
      }
   }

   /**
    * Get Select Options from Query
    *
    * @param string $query  Query
    * @return array|null  Select Options data or null
    */
   private function getSelectFromQuery($query, $recordnumber)
   {
      $q = str_replace("{{mainID}}", $recordnumber, $query);
      $result = mysqli_fetch_all(DB::query($q), MYSQLI_ASSOC);

      if ($result) {
         return $result;
      } else {
         return null;
      }
   }

   protected function renderRadioField($yesNo = false)
   {
      $radioOptions = $this->getRadioOptions();

      $output = "<div class='form-group cmsform formsection'>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label><br>";

      if ($yesNo) {
         $output .= "<input type='radio' name='" . $this->rowformfield["name"] . "' " . ($this->ContentValue == 'Yes' ?  'checked' : '') . " value='Yes'>&nbsp;&nbsp;Yes<br>";
         $output .= "<input type='radio' name='" . $this->rowformfield["name"] . "' " . ($this->ContentValue == 'No' ?  'checked' : '') . " value='No'>&nbsp;&nbsp;No";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
      } else {
         foreach ($radioOptions as $rOption) {
            if ($rOption["checked"] == 'Yes') {
               $checked = $rOption["checked"];
            }
            $output .= "<input type='radio' name='{$this->rowformfield["name"]}' value='{$rOption["value"]}' {$checked}> {$rOption["display"]}<br>";
         }
      }
      $output .= "</div>";

      return $output;
   }

   protected function renderCheckboxField()
   {
      $storedData = json_decode($this->ContentValue);
      $fieldOptions = $this->getRadioOptions();

      $output = "<div class='form-group cmsform formsection'>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label><br>";

      foreach ($fieldOptions as $fOption) {
         if (in_array($fOption["value"], $storedData)) {
            $checked = 'checked';
         } else {
            $checked = '';
         }
         $output .= "<input type='checkbox' name='{$this->rowformfield["name"]}[]' value='{$fOption["value"]}' {$checked}> {$fOption["display"]}<br>";
      }
      $output .= "</div>";

      return $output;
   }

   protected function renderColourPickerField()
   {
      $output = "<div class='form-group cmsform formsection {$this->itemclassname}'>";
      $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label>";

      $output .= "<input type='color' name='{$this->rowformfield["name"]}' class='form-control inputColor {$this->rowformfield["class"]}' id='exampleInputEmail1' value='{$this->ContentValue}' {$this->required}>";
      echo "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";

      return $output ;
   }

   protected function renderBootstrapDateField()
   {
      if ($this->rowformfield["allowedit"] == 'Yes') {
         $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
         $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

         if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
            if ($this->TypeDebug == 'Yes') {
               $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
            $output .= " - " . $this->ContentValue . "";
         }
         $output .= "</label>";
         $output .= "<div data-min-view='2' data-date-format='yyyy-mm-dd' class='input-group date datetime col-md-8 col-7' id='bDate-{$this->rowformfield['id']}'>";
         $output .= "<input name='{$this->rowformfield["name"]}' size='16' value='{$this->ContentValue}' readonly class='form-control {$this->rowformfield["class"]}' type='text' style='max-width:{$this->rowformfield["width"]}'>";
         $output .= "<span class='input-group-text btn btn-primary'><span class='fa fa-calendar'></span></span>";
         $output .= "</div>";
         $timestamp = strtotime($this->ContentValue);
         $formatted_date = date('D M j Y', $timestamp);
         $output .= "Current Value = <strong>{$formatted_date}</strong>";
         $output .= "</div>";
         $output .= "<script>
         $(function () {
            $('#bDate-{$this->rowformfield["id"]}').datepicker({
               autoclose: true,
               todayHighlight: true
            });
         });
         </script>";
      } else {
         $output = "<h4>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h4>";
      }

      return $output;
   }


   // DateTime Function # 28
   protected function renderDateTimeField()
   {
       if ($this->rowformfield["allowedit"] == 'Yes') {
           // Determine the formatted value
           if (!empty($this->ContentValue)) {
               // Check if ContentValue has a time part
               $datetime = strtotime($this->ContentValue);
               $formattedValue = date('Y-m-d\TH:i', $datetime);
           } else {
               // No value provided
               $formattedValue = '';
           }
   
           $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
           $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
   
           if ($this->rowformfield["tooltip"]) {
               $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
   
               if ($this->TypeDebug == 'Yes') {
                   $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
               }
           }
   
           $output .= "</label>";
           $output .= "<input class='form-control' type='datetime-local' id='datetimepicker' value='{$formattedValue}' name='{$this->rowformfield['name']}'>";
           $output .= "</div>";
       } else {
           $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
       }
   
       return $output;
   }
   

   // Bootstrap 5 Date  ## 29
   protected function renderBSDateField()
{
    if ($this->rowformfield["allowedit"] == 'Yes') {
        $formattedValue = !empty($this->ContentValue) ? date('Y-m-d', strtotime($this->ContentValue)) : '';

        $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
        $output .= "<label for='datePicker'>{$this->rowformfield["label"]}";

        if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
            if ($this->TypeDebug == 'Yes') {
                $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
        }

        $output .= "</label>";
        $output .= "<input class='form-control' type='date' id='datePicker' value='{$formattedValue}' name='{$this->rowformfield['name']}'>";
        $output .= "</div>";
    } else {
        $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
    }

    return $output;
}



// Bootstrap 5 DateTime ## 30
protected function renderBSDateTimeField()
{
    if ($this->rowformfield["allowedit"] == 'Yes') {
        $formattedValue = !empty($this->ContentValue) ? date('Y-m-d\TH:i', strtotime($this->ContentValue)) : '';

        $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
        $output .= "<label for='dateTimePicker'>{$this->rowformfield["label"]}";

        if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
            if ($this->TypeDebug == 'Yes') {
                $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
        }

        $output .= "</label>";
        $output .= "<input class='form-control' type='datetime-local' id='dateTimePicker' value='{$formattedValue}' name='{$this->rowformfield['name']}'>";
        $output .= "</div>";
    } else {
        $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
    }

    return $output;
}


// Bootstrap 5 Time ## 31
protected function renderBSTimeField()
{
    if ($this->rowformfield["allowedit"] == 'Yes') {
        $formattedValue = !empty($this->ContentValue) ? date('H:i', strtotime($this->ContentValue)) : '';

        $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
        $output .= "<label for='timePicker'>{$this->rowformfield["label"]}";

        if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
            if ($this->TypeDebug == 'Yes') {
                $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
        }

        $output .= "</label>";
        $output .= "<input class='form-control' type='time' id='timePicker' value='{$formattedValue}' name='{$this->rowformfield['name']}'>";
        $output .= "</div>";
    } else {
        $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
    }

    return $output;
}







   // Search Function
   protected function renderSearchField()
   {
      $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
      $output .= "<label for='{$this->rowformfield["name"]}'>{$this->rowformfield["label"]}";

      if ($this->rowformfield["tooltip"]) {
         $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

         if ($this->TypeDebug == 'Yes') {
            $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
         }
      }
      $output .= "</label><br>";
      $output .= "<input type='{$this->rowfield["type"]}' name='{$this->rowformfield["name"]}' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' value='{$this->ContentValue}' {$this->required} placeholder='{$this->rowformfield["placeholder"]}'>";
      $output .= "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";

      return $output;
   }

   protected function renderFileUploadField($recordnumber, $formnumber, $gallery)
   {
      $imgfld = 0;
      $img1 = 0;

      if ($this->rowformfield["showedit"] == 'Yes') {
         $output = "<div class='row'>";
         $output .= "<div class='col-md-8'>";
         $output .= "<div class='form-group cmsform'>";
         $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

         if ($this->rowformfield["tooltip"]) {
            $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

            if ($this->TypeDebug == 'Yes') {
               $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
            }
         }
         $output .= "</label>";

         if ($this->rowformfield["allowedit"] == 'Yes') { // Allow Edit  permitted
            $output .= "<input type='file' name='{$this->rowformfield["name"]}' class='form-control medium{$this->rowformfield["class"]}' id='exampleInputEmail1' >";
         }

         if ($this->ContentValue) {
            $imgfld = $imgfld + 1;
            $output .= "<br><a onclick='confirmDelete(\"{$this->rowformfield["name"]}\")' style='color:red; cursor: pointer;'>Delete</a> Current: Upload Image = {$this->ContentValue} | {$gallery['image1']} ";
            // Use confirm dialog box to delete image
            $output .= "<script>
            function confirmDelete(fieldName) {
               var x = confirm('Are you sure you want to delete?')
               const URL = 'deleteimage.php?id=$recordnumber&name='+ fieldName +'&frm=$formnumber'
               if (x)
                  window.location = URL;
               else
                  return false;
            }
            </script>";
         } else {
            $output .= "<br>Current: No file added";
         }
         $output .= "</div>";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
         $output .= "</div>";

         // Show Thumbnail of image
         $output .= "<div class='col-md-4'>";

         // Check if is a image or pdf 
         if ($this->rowformfield["mediatype"] == 'files') {
            $output .= "<div style='display: flex;align-items: center;justify-content: end;width: 100%;height: 100%;'>
               <i class='far fa-file-pdf' style='font-size: 2rem;'></i>
            </div>";
         } else {
            if ($this->ContentValue) {
               $rand = random_int(10000, 99999);
               $output .= "<img src='/filestore/{$this->rowformfield["mediatype"]}/{$this->rowformfield["file_folder_name"]}/{$this->ContentValue}?$rand' class='float-end ghai2' style='max-width: 120px;'>";
            } else {
               $output .= "<img src='/wccms/img/no-image.jpg' class='float-end ghai2' style='padding-top:30px;max-width: 80px;'>";
            }
         }

         $img1++;
         $output .= "</div>";
         $output .= "</div>";
      }else {
         $output = "<p>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></p>";
      }

      return $output;
   }

   protected function renderSelectFromTableField($recordnumber, $formnumber, $gallery)
   {
      if ($this->rowformfield["allowedit"] == 'Yes') {
         $queryfieldoptions = $this->getSelectFromQuery($this->rowformfield["sourcesql"], $recordnumber);
         $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
         $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

            if ($this->rowformfield["tooltip"]) {
               $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

               if ($this->TypeDebug == 'Yes') {
                  $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
               }
            }
         $output .= "</label>";
         $output .= "<select name='{$this->rowformfield["name"]}'  class='form-control {$this->rowformfield["class"]}' size='1'>";

         foreach ($queryfieldoptions as $select) {
            if ($this->ContentValue == $select["id"]) {
               $output .= "<option value='{$select["id"]}' selected='selected'>{$select["name"]}</option>";
            } else {
               if ($formnumber == 23) {
                  $gallery_folder_name = $gallery['folder_name'];

                  $output .= "<option " . (($gallery_folder_name == $select["name"]) ? 'selected="selected"' : "") . " value='{$select["name"]}'>{$select["name"]}</option>";
               } else {
                  $output .= "<option value='{$select["id"]}'>{$select["name"]}</option>";
               }
            }
         }
         $output .= "</select>";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
         $output .= "</div>";
      } else {
         $output = "<h4>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h4>";
      }

      return $output;
   }

   protected function renderTextAreaTinymceField()
   {
      $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
         $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
            if ($this->rowformfield["tooltip"]) {
               $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

               if ($this->TypeDebug == 'Yes') {
                  $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]} | WYGIWYG)</span>";
               }
            }
         $output .= "</label>";
         $output .= "<textarea name='{$this->rowformfield["name"]}' id='tinymcetextarea' class='form-control {$this->rowformfield["class"]}' rows='10' >{$this->ContentValue}</textarea>";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";

      return $output;
   }

   protected function renderTextAreaField()
   {
      $output = "<div class='form-group cmsform formsection  {$this->itemclassname}' style=''>";
         $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
            if ($this->rowformfield["tooltip"]) {
               $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
               if ($this->TypeDebug == 'Yes') {
                  $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
               }
            }
         $output .= "</label>";
         $output .= "<textarea name='{$this->rowformfield["name"]}' id='plaintextarea' class='form-control {$this->rowformfield["class"]}' rows='10'>{$this->ContentValue}</textarea> ";
         $output .= "<span>{$this->rowformfield["comment"]}<span>";
      $output .= "</div>";
      return $output;
   }

   protected function renderDateField()
   {
      if ($this->rowformfield["allowedit"] == 'Yes') {
         $output = "<div class='form-group cmsform formsection'  style='{$this->itemclass}'>";
            $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
            if ($this->rowformfield["tooltip"]) {
               $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

               if ($this->TypeDebug == 'Yes') {
                  $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
               }
            }
            $output .= "</label>";
            $output .= "<input class='form-control' type='date' id='datetimepicker' value='{$this->ContentValue}' name='{$this->rowformfield['name']}'>";
         $output .= "</div>";
      } else {
         $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
      }
      return $output;
   }

   protected function renderSelectField()
   {
      if ($this->rowformfield["allowedit"] == 'Yes') {
         $fieldOptions = $this->getRadioOptions();

         $output = "<div class='form-group cmsform formsection' style='{$this->itemclass}'>";
            $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";
               if ($this->rowformfield["tooltip"]) {
                  $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

                  if ($this->TypeDebug == 'Yes') {
                     $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
                  }
               }
            $output .= "</label>";
            $output .= "<select name='{$this->rowformfield["name"]}'  class='form-control {$this->rowformfield["class"]}' size='1'>";

            foreach ($fieldOptions as $fOption) {
               if ($this->ContentValue == $fOption["value"]) {
                  $output .= "<option value='" . $fOption["value"] . "' selected='selected'>" . $fOption["display"] . "</option>";
               } else {
                  $output .= "<option value='" . $fOption["value"] . "' >" . $fOption["display"] . "</option>";
               }
            }
            $output .= "</select>";
            $output .= "<span>{$this->rowformfield["comment"]}<span>";
         $output .= "</div>";
      } else {
         $output = "<h6>{$this->rowformfield["label"]} = <strong>{$this->ContentValue}</strong></h6>";
      }

      return $output;
   }

   protected function renderTimeField()
   {
      if ($this->rowformfield["allowedit"] == 'Yes') {
         $output = "<div class='form-group cmsform formsection' style='{$this->itemclass}'>";
            $output .= "<label for='{$this->rowformfield["name"]}'>{$this->rowformfield["label"]}";
               if ($this->rowformfield["tooltip"]) {
                  $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";
                  if ($this->TypeDebug == 'Yes') {
                     $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
                  }
               }
            $output .= "</label><br>";
            $output .= "<input type='{$this->rowfield["type"]}' name='{$this->rowformfield["name"]}' class='form-control {$this->rowformfield["class"]}' id='exampleInputEmail1' {$this->required} value='{$this->ContentValue}'>";
               $output .= "<h6>{$this->rowformfield["label"]} = <strong>" . date('g:i a', strtotime($this->ContentValue)) . "</strong></h6>";
               $output .= "<span>{$this->rowformfield["comment"]}<span>";
         $output .= "</div>";
      } else {
         $output = "<h6>{$this->rowformfield["label"]} = <strong>" . date('g:i a', strtotime($this->ContentValue)) . "</strong></h6>";
      }
      return $output;
   }

   private function getTable($form_id)
   {
      $query = "SELECT * FROM `cms_form` 
      WHERE `id` = '" . $form_id . "' ";
      $form = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);
      if ($form) {
         $query = "SELECT * FROM `cms_table` 
         WHERE `id` = '" . $form['table'] . "' ";
         $table = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);
         if ($table) {
            return $table;
         } else {
            return null;
         }
      } else {
         return null;
      }
   }

   private function getProductImages($recordnumber)
   {
      $query = "SELECT * FROM `pro_product_images` 
       WHERE productid = $recordnumber 
       ORDER BY sort ASC";
      $images = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if ($images) {
         return $images;
      } else {
         return [];
      }
   }

   private function getGalleryImages($recordnumber, $form)
   {
      $query = "SELECT * FROM `gallery` 
      WHERE `record_id` = $recordnumber 
      AND `form_id` = $form
      ORDER BY sort ASC";
      $images = mysqli_fetch_all(DB::query($query), MYSQLI_ASSOC);

      if ($images) {
         return $images;
      } else {
         return [];
      }
   }

   /**
    * Get Content 
    *
    * @param string $table  Table name
    * @param int $id  Content id
    * @return array|null  Content data or null
    */
   public function getContent($table, $id)
   {
      $query = "SELECT * FROM `$table` 
      WHERE `id` = '$id' ";
      $content = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);

      if ($content) {
         return $content;
      } else {
         return null;
      }
   }

   protected function renderGalleryField($recordnumber, $formnumber)
   {
      $output = '<form action="controllers/image_processor.php?id=' . $recordnumber . '&frm=' . $formnumber . '" method="post" enctype="multipart/form-data" class="dropzone" id="my-dropzone">
         <div class="fallback">
            <input type="file" name="images[]" multiple>
			</div>
		</form>' ;

         $output .= "<button type='button' class='btn btn-success' id='submit-all'>Upload Images</button>" ;
         $output .= "<div id='response' style='background-color: white;'>
      </div>";

         $output .= '<script>
         const MAX_FILES = ' . $this->maxGalleryImages . ';
         Dropzone.options.myDropzone = {
            paramName: "images", // Nombre del parámetro de los archivos
            // maxFilesize: 2, // Tamaño máximo de los archivos en MB
            acceptedFiles: ".jpg,.jpeg,.png,.gif", // Extensiones de archivo aceptadas
            dictDefaultMessage: "Drag your images here or click to select them", // Mensaje predeterminado
            autoProcessQueue: false, // Deshabilitar el procesamiento automático
            // accept: function(file, done) {
            //    done();
            // },
            maxFiles: MAX_FILES, // Número máximo de archivos
            parallelUploads: MAX_FILES, // Número máximo de archivos que se pueden cargar al mismo tiempo
            uploadMultiple: true, // Habilitar la carga de múltiples archivos
            init: function() {
               var myDropzone = this;
               var submitButton = document.querySelector("#submit-all");
               submitButton.addEventListener("click", function() {
                  myDropzone.processQueue(); // Procesar la cola de archivos
                  // document.getElementById("my-dropzone").submit(); // Enviar el formulario
               });
            },
            success: function(file, response) {
               console.log(response);
               var div_response = document.querySelector("#response");
               div_response.innerHTML = response;
            }
         };
         </script>';

      if ($formnumber == 11) { // Product 
         $images_data = $this->getProductImages($recordnumber);
      } 
      else 
      { // Other forms
         // $table = $this->getTable($formnumber);
         // $content = $this->getContent($table['name'], $recordnumber);
         // $colName = $this->rowformfield["name"];
         // $images_data = $content[$colName];
         $images_data = $this->getGalleryImages($recordnumber, $formnumber);
      }

      $countimg = count($images_data);

      $output .= "<form method='post' enctype='multipart/form-data'>
         <center>
            <table class='table'>
               <tbody id='sortable'>";

               if ($formnumber == 11) { // Product
                  $i = 0;

                  foreach ($images_data as $resimglist) {
                     $img_id = $resimglist["id"];
                     $img_src = "/filestore/images/products/sm/" . $resimglist["image"];
                     $img_name = $resimglist["image"];
                     $alttag = $resimglist["alttag"];
                     $img_caption = $resimglist["caption"];
                     $sort = $resimglist["sort"];
                     $showonweb = $resimglist["showonweb"];

                     if ($showonweb == "Yes") {
                        $checked = "checked";
                     } else {
                        $checked = "";
                     }

                     $sort += 1;

                     $output .= "<tr id='$img_id'>
                        <td><img src='$img_src' width=100px></td>
                        <td>
                           Alt <input type='text' class='form-control' style='width:25%' value='$alttag' name='alt-$img_id'>
                           Caption <input type='text' class='form-control ' style='width:25%' value='$img_caption' name='caption-$img_id'>
                           <div class='custom-control custom-switch' style='margin-left:10px;margin-top:10px'>
                              <input type='checkbox'  $checked  class='custom-control-input' id='customSwitch$i' onchange='check(this,$img_id)'>
                              <label class='custom-control-label' for='customSwitch$i'>Show on Web</label>
                              <span style='font-size:14px;padding-left:20px;'><em>Filename:</em> $img_name</span>
                           </div>
                        </td>
                        <td colspan=2>
                           <a href='remove.img.php?img=$img_id&frm=$formnumber&id=$recordnumber'><i class='far fa-trash-alt'></i></a>
                        </td>
                     </tr>";
                     $i++;
                  }
               } 
               else 
               { // Other forms
                  $i = 0;

                  foreach ($images_data as $resimglist) {
                     $img_id = $resimglist["id"];
                     $img_src = "/filestore/{$resimglist['folder_name']}/" . $resimglist["image"];
                     $img_name = $resimglist["image"];
                     $alttag = $resimglist["alttag"];
                     $img_caption = $resimglist["caption"];
                     $sort = $resimglist["sort"];
                     $showonweb = $resimglist["showonweb"];

                     if ($showonweb == "Yes") {
                        $checked = "checked";
                     } else {
                        $checked = "";
                     }

                     $sort += 1;

                     $output .= "<tr id='$img_id'>
                        <td><img src='$img_src' width=100px></td>
                        <td>
                           Alt <input type='text' class='form-control' style='width:25%' value='$alttag' name='alt-$img_id'>
                           Caption <input type='text' class='form-control ' style='width:25%' value='$img_caption' name='caption-$img_id'>
                           <div class='custom-control custom-switch' style='margin-left:10px;margin-top:10px'>
                              <input type='checkbox'  $checked  class='custom-control-input' id='customSwitch$i' onchange='check(this,$img_id)'>
                              <label class='custom-control-label' for='customSwitch$i'>Show on Web</label>
                              <span style='font-size:14px;padding-left:20px;'><em>Filename:</em> $img_name</span>
                           </div>
                        </td>
                        <td colspan=2>
                           <a href='remove.img.php?img=$img_id&frm=$formnumber&id=$recordnumber'><i class='far fa-trash-alt'></i></a>
                        </td>
                     </tr>";
                     $i++;
                  }

                  /*
                     // $images_data = explode(',', $images_data); // Convert string to array
                     // $images_data = array_filter($images_data, function ($value) {
                     //    return $value !== '';
                     // }); // Remove empty values (last value is empty)

                     // for ($i = 0; $i < count($images_data); $i++) {
                     //    $img_id = $i;
                     //    $img_src = "/filestore/{$this->rowformfield['mediatype']}/" . ($this->rowformfield['file_folder_name'] != '' ? $this->rowformfield['file_folder_name'] . '/' : '') . $images_data[$i];

                     //    $img_name = $images_data[$i];
                     //    $alttag = preg_replace('/\\.[^.\\s]{3,4}$/', '', $img_name); // Remove extension
                     //    $img_caption = '';
                     //    $sort = $i;
                     //    $showonweb = $this->rowformfield['showonweb'];

                     //    if ($showonweb == "Yes") {
                     //       $checked = "checked";
                     //    } else {
                     //       $checked = "";
                     //    }

                     //    $sort += 1;

                     //    $output .= "<tr id='$img_id'>
                     //       <td><img src='$img_src' width=100px></td>
                     //       <td>
                     //          <span style='font-size:14px;padding-left:20px;'><em>Filename:</em> $img_name</span>
                     //       </td>
                     //       <td colspan=2>
                     //          <a href='remove.img.php?id=$img_id&frm=$formnumber'><i class='far fa-trash-alt'></i></a>
                     //       </td>
                     //    </tr>";
                     // }
                  */
               
               }
               $output .= "</tbody>" ;
            $output .= "</table>" ;
         $output .= "</center>" ;

         $output .= "<center>" ;
            if ($countimg > 0) {
               $output .= "<input type='submit' name='updateGallery' class='btn btn-success' value='Update Images' style='width:20%'>";
            } 
            else 
            {
               $output .= "<input type='submit' name='updateGallery' class='btn btn-success' value='Update Images' style='width:20%' disabled>";
            }

         $output .= "</center>" ;
      $output .= "</form>" ;

      $output .= "<hr>
      <p>Images in gallery = $countimg</p>" ;

      return $output;
   }

   protected function renderDownloadCV($baseURL)
   {
      $ContentValue = stripslashes($this->ContentValue);
      $ContentValue = $this->escape_html($ContentValue);

      $output = "<button type='button' class='btn btn-primary' onclick='window.open(\"$baseURL/wccms/uploadedfiles/" . $ContentValue . "\", \"_blank\")'>Download </button>";

      return $output;
   }

   // private function getGalleryData() {
   //    $query = "SELECT * FROM `gallery`
   //    WHERE `image1` = '{$this->ContentValue}'";
   //    $result = mysqli_fetch_array(DB::query($query), MYSQLI_ASSOC);

   //    if ($result) {
   //       return $result;
   //    } else {
   //       return null;
   //    }
   // }

   // protected function renderImageFromGalleryField($baseURL, $recordnumber, $formnumber) {
   //    if ($this->rowformfield["showedit"] == 'Yes') {
   //       $galleryData = $this->getGalleryData();

   //       $output = "<div class='form-group cmsform'>";

   //       if (isset($galleryData)) {
   //          $output .= "<img src='" . $baseURL . "/filestore/" . $this->rowformfield["mediatype"] . "/" . $galleryData["folder_name"] . "/" . $this->ContentValue . "' class='float-end ghai1' style='max-width: 120px;' alt='" . $galleryData["alttag"] . " | " . $this->ContentValue . "' title='"  . $galleryData["caption"] . " | " . $this->ContentValue . "'>";
   //       }

   //       $output .= "<label for='exampleInputEmail1'>{$this->rowformfield["label"]}";

   //       if ($this->rowformfield["tooltip"]) {
   //          $output .= "<a href='#' data-bs-toggle='tooltip' title='{$this->rowformfield["tooltip"]}'>{$this->infomark}</a>";

   //          if ($this->TypeDebug == 'Yes') {
   //             $output .= "<span style='color:#dddddd;'>&nbsp;&nbsp;({$this->rowfield["id"]})</span>";
   //          }
   //       }
   //       $output .= "</label>";

   //       if ($this->rowformfield["allowedit"] == 'Yes') {
   //          // Gallery Image
   //          $output .= "<br><button type='button' class='btn btn-info btn-lg' id=" . $this->rowformfield["class"] . " onclick='check_field(this.id)'; data-bs-toggle='modal' data-bs-target='#myModal'>Upload From Gallery</button><span id=" . "txt" . $this->rowformfield["class"] . "></span>";

   //          $output .=  "<br>OR <a href='" . $baseURL . "/truskacms/recordAdd.php?id=23' target='_blank'><i class='fas fa-plus-circle' style='color:green;'></i> Add new Image to Gallery</a> -|-  <a href='" . $baseURL . "/wccms/recordEditv4.php?frm=" . $formnumber . "&id=" . $recordnumber . "&remove=gallery-image'><i class='fas fa-minus-circle' style='color:red;'></i> Remove</a>";
   //       }

   //       if (isset($galleryData)) {
   //          $output .= "<br>Current: Gallery Image = " . $this->ContentValue . " in folder: "  . $galleryData["folder_name"] . " ";
   //       } else {
   //          $output .= "<br>No image loaded";
   //       }

   //       $output .= '<input type="hidden" value="' . $this->ContentValue . '" class="' . $this->rowformfield["class"] . '" name="' . $this->rowformfield["class"] . '" />';
   //       $output .= "</div>";
   //    }

   //    return $output;
   // }
}
