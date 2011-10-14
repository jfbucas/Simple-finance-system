<script language="JavaScript">
/********************************************************************* 
* validation.js                                                      *
* Form validation functions for validating entire forms (onSubmit)   *
* or on-the-fly validation. (onBlur / onChange)                      *
* Copyright 2005 John Frank and BC Tree Fruits Limited.  
* with modifications from Andrres Jimenez and Dublin Institute       *
* for Advanced Studies.                                              *
*********************************************************************/

// Functions for onSubmit form validation.

function isAlpha(val, len, req) {
// Check for "a-z", "A-Z", " ", and optionally length.
   if ((val.value.length == 0) && (req == "R")) {
      return false;
   }
   if (val.value.length != 0) {
      for (i = 0; i < val.value.length; i++) {
         var ch = val.value.charAt(i);
         if ((ch >= "A" && ch <= "Z") || (ch >= "a" && ch <= "z") || (ch == " ")) {
            continue;
         } else {
            return false;
         }
      }
      if (len != 0) {
         if (val.value.length != len) {
            return false;
         }
      }
   }
   return true;
}

function isDate(val, req) {
// Check for a correctly formatted date.
   var date_fmt = "<?php echo $cfg['date_fmt']; ?>";
   var shortform = /^[\+\-]{1}[0-9]+$/;
   if ((date_fmt == "usa") || (date_fmt == "int")) {
      var longform = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
   } else {
      var longform = /^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/;
   }
   if ((val.value.length == 0) && (req == "R")) {
      return false;
   }
   if (val.value.length != 0) {
      if (!shortform.test(val.value)) {
         if (!longform.test(val.value)) {
            return false;
         }
      }
   }
   return true;
}

function isEmail(val, req) {
// Check for a correctly formatted email address.
   if ((val.value.length == 0) && (req == "R")) {
      return false;
   }
   if (val.value.length != 0) {
      //var emailformat = /^\w+@\w+\.\w+/;   // Strict
      var emailformat = /^[\w\.]+@\w+\.\w+/;   // Strict
      // var emailformat = /^.+@.+/;       // Less strict
      if (!emailformat.test(val.value)) {
         return false;
      }
   }
   return true;
}

function isNum(val, len, req) {
// Check for "0-9", ".", "-", and optionally length.
   if ((val.value.length == 0) && (req == "R")) {
      return false;
   }
   if (val.value.length != 0) {
      for (i = 0; i < val.value.length; i++) {
         var ch = val.value.charAt(i);
         if ((ch >= "0" && ch <= "9") || (ch == ".") || (ch == "-")) {
            continue;
         } else {
            return false;
         }
      }
      if (len != 0) {
         if (val.value.length != len) {
            return false;
         }
      }
   }
   return true;
}

function isPhone(val, req) {
// Check for a correctly formatted phone number.
   var phone_fmt = "<?php echo $cfg['phone_fmt']; ?>";
   if (phone_fmt == "nam") {
      var phoneformat = /^[0-9]{3}\-[0-9]{3}\-[0-9]{4}$/;
   } else {
      var phoneformat = /^[0-9 \+\-]+$/;
   }
   if ((val.value.length == 0) && (req == "R")) {
      return false;
   }
   if (val.value.length != 0) {
      if (!phoneformat.test(val.value)) {
         return false;
      }
   }
   return true;
}

function isText(val, len, req) {
// Check that field is not empty and optionally length.
   if ((val.value.length == 0) && (req == "R")) {
      return false;
   }
   if (val.value.length != 0) {
      if (len != 0) {
         if (val.value.length != len) {
            return false;
         }
      }
   }
   return true;
}

function maxLenText(val, len) {
// Check that field's length is not exceeded.
      if (len != 0) {
         if (val.value.length > len) {
            return false;
         }
      }
   return true;
}

// The same functions modified for onBlur / onChange form validation

function BisAlpha(val, len, req) {
// Check for "a-z", "A-Z", " ", and optionally length.
   if ((val.value.length == 0) && (req == "R")) {
      alert("This field is required.");
      val.focus();
      val.select();
      return false;
   }
   if (val.value.length != 0) {
      for (i = 0; i < val.value.length; i++) {
         var ch = val.value.charAt(i);
         if ((ch >= "A" && ch <= "Z") || (ch >= "a" && ch <= "z") || (ch == " ")) {
            continue;
         } else {
            alert("This field must contain ALPHA characters.");
            val.focus();
            val.select();
            return false;
         }
      }
      if (len != 0) {
         if (val.value.length != len) {
            alert("This field must contain " + len + " ALPHA characters.");
            val.focus();
            val.select();
            return false;
         }
      }
   }
   return true;
}

function BisDate(val, req) {
// Check for a correctly formatted date.
   var date_fmt = "<?php echo $cfg['date_fmt']; ?>";
   var shortform = /^[\+\-]{1}[0-9]+$/;
   if ((date_fmt == "usa") || (date_fmt == "int")) {
      var longform = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
   } else {
      var longform = /^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/;
   }
   if ((val.value.length == 0) && (req == "R")) {
      alert("This field is required.");
      val.focus();
      val.select();
      return false;
   }
   if (val.value.length != 0) {
      if (!shortform.test(val.value)) {
         if (!longform.test(val.value)) {
            alert ("This field must contain a correctly formatted date.");
            val.focus();
            val.select();
            return false;
         }
      }
   }
   return true;
}

function BisEmail(val, req) {
// Check for a correctly formatted email address.
   if ((val.value.length == 0) && (req == "R")) {
      alert("This field is required.");
      val.focus();
      val.select();
      return false;
   }
   if (val.value.length != 0) {
      var emailformat = /^\w+@\w+\.\w+/;   // Strict
      // var emailformat = /^.+@.+/;       // Less strict
      if (!emailformat.test(val.value)) {
         alert("This field must contain a valid E-Mail address.");
         val.focus();
         val.select();
         return false;
      }
   }
   return true;
}

function BisNum(val, len, req) {
// Check for "0-9", ".", "-", and optionally length.
   if ((val.value.length == 0) && (req == "R")) {
      alert("This field is required.");
      val.focus();
      val.select();
      return false;
   }
   if (val.value.length != 0) {
      for (i = 0; i < val.value.length; i++) {
         var ch = val.value.charAt(i);
         if ((ch >= "0" && ch <= "9") || (ch == ".") || (ch == "-")) {
            continue;
         } else {
            alert("This field must contain NUMERIC characters.");
            val.focus();
            val.select();
            return false;
         }
      }
      if (len != 0) {
         if (val.value.length != len) {
            alert("This field must contain " + len + " NUMERIC characters.");
            val.focus();
            val.select();
            return false;
         }
      }
   }
   return true;
}

function BisPhone(val, req) {
// Check for a correctly formatted phone number.
   var phone_fmt = "<?php echo $cfg['phone_fmt']; ?>";
   if (phone_fmt == "nam") {
      var phoneformat = /^[0-9]{3}\-[0-9]{3}\-[0-9]{4}$/;
   } else {
      var phoneformat = /^[0-9 \+\-]+$/;
   }
   if ((val.value.length == 0) && (req == "R")) {
      alert("This field is required.");
      val.focus();
      val.select();
      return false;
   }
   if (val.value.length != 0) {
      if (!phoneformat.test(val.value)) {
         alert ("This field must contain a correctly formatted phone number.");
         val.focus();
         val.select();
         return false;
      }
   }
   return true;
}

function BisText(val, len, req) {
// Check that field is not empty and optionally length.
   if ((val.value.length == 0) && (req == "R")) {
      alert("This field is required.");
      val.focus();
      val.select();
      return false;
   }
   if (val.value.length != 0) {
      if (len != 0) {
         if (val.value.length != len) {
            alert("This field must contain " + len + " ALPHA-NUMERIC characters.");
            val.focus();
            val.select();
            return false;
         }
      }
   }
   return true;
}

function isConfirmed(message) {
// Pop up a confirmation box. Return true for OK and false for Cancel.
   if (!confirm(message)) {
      return false;
   }
   return true;
}

// The following functions are specific to AssetMan.

function valid_asset_form(form) {
   var message="", error=0;
   if (!isNum(form.tag,7,'R')) { message+="Asset Tag must be a 7 digit number.\n"; error=1; }
   if (!isText(form.make,0,'R')) { message+="Make is required.\n"; error=1; }
   if (!isText(form.model,0,'R')) { message+="Model is required.\n"; error=1; }
   if (!isText(form.serial,0,'R')) { message+="Serial is required.\n"; error=1; }
//   if (!isNum(form.po_number,5,'N')) { message+="PO must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.price,0,'N')) { message+="Purchase Price must be a number.\n"; error=1; }
   if (!isDate(form.date,'R')) { message+="Receive Date must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (!isDate(form.warranty,'N')) { message+="Warranty Expires must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_asset_from_po_form(form) {
   var message="", error=0;
   if (!isText(form.tag,0,'R')) { message+="Asset Tag is required.\n"; error=1; }
   if (!isText(form.make,0,'R')) { message+="Make is required.\n"; error=1; }
   if (!isText(form.model,0,'R')) { message+="Model is required.\n"; error=1; }
   if (!isText(form.serial,0,'R')) { message+="Serial is required.\n"; error=1; }
//   if (!isNum(form.po_number,5,'N')) { message+="PO must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.price,0,'N')) { message+="Purchase Price must be a number.\n"; error=1; }
   if (!isDate(form.date,'R')) { message+="Receive Date must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (!isDate(form.warranty,'N')) { message+="Warranty Expires must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_config_form(form) {
   var message="", error=0;
   if (!isText(form.name,0,'R')) { message+="Descriptive Name is required.\n"; error=1; }
   if (!isNum(form.tag1,5,'N')) { message+="Tag 1 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag2,5,'N')) { message+="Tag 2 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag3,5,'N')) { message+="Tag 3 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag4,5,'N')) { message+="Tag 4 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag5,5,'N')) { message+="Tag 5 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag6,5,'N')) { message+="Tag 6 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag7,5,'N')) { message+="Tag 7 must be a 5 digit number.\n"; error=1; }
   if (!isNum(form.tag8,5,'N')) { message+="Tag 8 must be a 5 digit number.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_item_form(form) {
   var message="", error=0;
   if (!isText(form.make,0,'R')) { message+="Make is required.\n"; error=1; }
   if (!isText(form.model,0,'R')) { message+="Model is required.\n"; error=1; }
   if (!isText(form.serial,0,'R')) { message+="Serial is required.\n"; error=1; }
//   if (!isNum(form.po_number,5,'N')) { message+="PO must be a 5 digit number.\n"; error=1; }
   if (!isDate(form.date,'R')) { message+="Receive Date must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (!isDate(form.warranty,'N')) { message+="Warranty Expires must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_po_line_form(form) {
   var message="", error=0;
   if (!isNum(form.qty,0,'R')) { message+="Quantity is required.  The amount of item you want to order.\n"; error=1; }
   //if (!isText(form.unit,0,'R')) { message+="Shortname is required.  This is just one word for your item.\n"; error=1; }
   if (!isText(form.descrip,0,'R')) { message+="Description is required.\n"; error=1; }
   if (!maxLenText(form.descrip,255)) { message+="Description field has a 255 characters limit.\n"; error=1; }
   if (!isNum(form.unit_price,0,'N')) { message+="Unit Price must be a number.   Please do not include any currency sign($,euro,...) and avoid spaces and coma.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_tr_line_form(form) {
   var message="", error=0;
   if (!isText(form.description,0,'R')) { message+="Description is required.\n"; error=1; }
   //if (!isText(form.comment,0,'R')) { message+="Description is required.\n"; error=1; }
   if (!maxLenText(form.description,255)) { message+="Description field has a 255 characters limit.\n"; error=1; }
   if (!maxLenText(form.comment,255)) { message+="Comment field has a 255 characters limit.\n"; error=1; }
   if (!isNum(form.price,0,'N')) { message+="Price must be a number.   Please do not include any currency sign($,euro,...) and avoid spaces and coma.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_er_line_form(form) {
   var message="", error=0;
   if (!isText(form.description,0,'R')) { message+="Description is required.\n"; error=1; }
   //if (!isText(form.comment,0,'R')) { message+="Descrddiption is required.\n"; error=1; }
   if (!maxLenText(form.description,255)) { message+="Description field has a 255 characters limit.\n"; error=1; }
   if (!maxLenText(form.comment,255)) { message+="Comment field has a 255 characters limit.\n"; error=1; }
   if (!isNum(form.price,0,'N')) { message+="Price must be a number.   Please do not include any currency sign($,euro,...) and avoid spaces and coma.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_inv_line_form(form) {
   var message="", error=0;
   if (!isDate(form.rcv_date,'R')) { message+="Rcv Date must be a correctly formatted date or a date shortcut.\n"; error=1; }
   if (!isText(form.rcv_by,0,'R')) { message+="Rcv By is required.\n"; error=1; }
   if (!isNum(form.qty,0,'R')) { message+="Quantity is required.\n"; error=1; }
   if (!isNum(form.unit_price,0,'R')) { message+="Price is required.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_section_form(form) {
   var message="", error=0;
   if (!isText(form.name,0,'R')) { message+="Name is required.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_vendor_form(form) {
   var message="", error=0;
   if (!isText(form.name,0,'R')) { message+="Name is required.\n"; error=1; }
   if (!isText(form.address1,0,'R')) { message+="Address 1 is required.\n"; error=1; }
   if (!isText(form.city,0,'R')) { message+="City is required.\n"; error=1; }
   //if (!isText(form.province,0,'R')) { message+="Province is required.\n"; error=1; }
   //if (!isText(form.p_code,0,'R')) { message+="Postal Code is required.\n"; error=1; }
   if (!isPhone(form.main_phone,'N')) { message+="Main Phone must be a correctly formatted phone number.\n"; error=1; }
   //if (!isPhone(form.main_fax,'N')) { message+="Main FAX must be a correctly formatted phone number.\n"; error=1; }
   if (!isEmail(form.main_email,'N')) { message+="Main E-Mail must be a correctly formatted e-mail address.\n"; error=1; }
   //if (!isPhone(form.tech_phone,'N')) { message+="Tech Phone must be a correctly formatted phone number.\n"; error=1; }
   //if (!isPhone(form.tech_fax,'N')) { message+="Tech FAX must be a correctly formatted phone number.\n"; error=1; }
   //if (!isEmail(form.tech_email,'N')) { message+="Tech E-Mail must be a correctly formatted e-mail address.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}
function valid_vendor_category_form(form) {
   var message="", error=0;
   if (!isText(form.name,0,'R')) { message+="Name is required.\n"; error=1; }
   if (error==1) { alert(message); return false; }
   return true;
}

</script>
