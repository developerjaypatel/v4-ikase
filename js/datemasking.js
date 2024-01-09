var dtFormat = "MM/DD/YYYY";
var sepChar = '/';
var fullDateMask = /^[01][0-9]\/[0-3][0-9]\/[2][0-9][0-9][0-9]/;
var lastKeyStrokeVal;
var currMask;
var monthVal;
var dayVal;
var yearVal;
var autoFillVal = '=';
var day1Val;
var day2Val;


//////////////////////////////////////////////////////////
// returnCurrentDate() : return the current system date //
// in MM/DD/YYYY format                                 //
//////////////////////////////////////////////////////////
function returnCurrentDate()
{
   d = new Date();
   m = parseInt(d.getMonth())+1;
   if (m < 10)
   {
      m = '0' + m;
   }
   return(m + sepChar + d.getDate() + sepChar + d.getYear());
}

////////////////////////////////////////////////////////////
// scrutinizeKeyVal() : apply mask to the keystroke value //
// when each keystroke is typed                           //
////////////////////////////////////////////////////////////
function scrutinizeKeyVal(obj)
{
   ////////////////////////////////////////////////////////////////////
   // If using IE, the "String.fromCharCode(window.event.keyCode)"   //
   // will return the key value pressed. For Netscape, the "which"   //
   // keyword will return the keyvalue. NOT TESTED WITH NETSCAPE YET //
   ////////////////////////////////////////////////////////////////////

   var length = parseInt(obj.value.length);

   lastKeyStrokeVal = String.fromCharCode(window.event.keyCode); // IE Only
   if (lastKeyStrokeVal == autoFillVal)
   {
      obj.value = returnCurrentDate();
      return -1;
   }
   /////////////////////////////////////////////////////////
   // The date format is mm/dd/yyyy and leading zeros are // 
   // required in the Months and Days fields              //
   /////////////////////////////////////////////////////////

   /////////////////////////////////////////////
   // FIRST CHARACTER TYPED - month field     //
   // The first char typed should be a 0 or 1 //
   /////////////////////////////////////////////
   if (length == 0)    
   {
     currMask = /^[0-1]/;
     if (!compareValue(lastKeyStrokeVal, currMask))
     {
        return -1;
     }
   }
   ///////////////////////////////////////////////////////
   // SECOND CHARACTER TYPED - month field              //
   // if first char is 1, second char may only be 0,1,2 //
   ///////////////////////////////////////////////////////
   if (length == 1)  
   {  
     if (obj.value.charAt(length -1) == 1)
     {
        currMask = /^[0-2]/; //months 10,11,12
     }
     else
     {
        currMask = /^[1-9]/; //months 01-09
     }
     if (!compareValue(lastKeyStrokeVal, currMask))  
     {
        return -1;
     }
     //////////////////////////////////
     // capture the month value and  //
     // Autofill the first delimiter //
     //////////////////////////////////
     monthVal = obj.value + lastKeyStrokeVal;   
     return 1;                                  
   }
   ////////////////////////////////////////////
   // THE THIRD OR SIXTH CHARACTER TYPED     //
   // This character should be the delimiter //
   // char for the date format "MM/DD/YYYY"  //
   ////////////////////////////////////////////
   if ((length == 2) || (length == 5))  
   {
      currMask = /^\//;
      if (!compareValue(lastKeyStrokeVal, currMask))
      {
         return -1;
      }
   }
   ///////////////////////////////////////////////////////
   // THE FOURTH CHARACTER TYPED - day field            //
   // The fourth char typed should be a number...0,1,2,3//
   // We can't check for leap year yet because we don't //
   // have the year value yet. This will need to be     //
   // done after the date field is populated...         //
   ///////////////////////////////////////////////////////
   if (length == 3)    
   { 
     currMask = /^[0-3]/;
     if (!compareValue(lastKeyStrokeVal, currMask))
     {  
        return -1;
     }
     day1Val = lastKeyStrokeVal;
   }
   //////////////////////////////////////////////////////////
   // THE FIFTH CHARACTER TYPED - day field                //
   // The fifth char typed should be a number              //
   // if first char is 0, second char may only be 1-9      //
   // if first char is 1 or 2, second char may only be 0-9 //
   // if first char is 3, second char may only be 0-1      //
   //////////////////////////////////////////////////////////
   if (length == 4)    
   {
     if (day1Val == 0)
     {
        currMask = /^[1-9]/;
     }
     else if ((day1Val == 1) || (day1Val == 2))
     {
        currMask = /^[0-9]/;
     }
     else if (day1Val == 3)
     {
        currMask = /^[0-1]/;
     }
     else
     {
        return -1;
     }
     if (!compareValue(lastKeyStrokeVal, currMask))
     {
        return -1;
     }
     day2Val = lastKeyStrokeVal;
     return 1;
   }
   //////////////////////////////////////////////////////////////
   // THE SEVENTH CHARACTER TYPED - year field                 //
   // Safe to assume this character is going to be a 1 or 2... //
   //////////////////////////////////////////////////////////////
   if (length == 6)    
   {
      currMask = /^[1-2]/;
      if (!compareValue(lastKeyStrokeVal, currMask))
      {
         return -1;
      }
   }
   ///////////////////////////////////////////////////////////
   // THE EIGHTH, NINTH, TENTH CHARACTER TYPED - year field //
   ///////////////////////////////////////////////////////////
   if ((length == 7) || (length == 8) || (length == 9))   
   {
      currMask = /^[0-9]/;
      if (!compareValue(lastKeyStrokeVal, currMask))
      {
         return -1;
      }
   } 
   /////////////////////////////////////////////////////////
   // Finally, do a mask check for the date val so far... //
   /////////////////////////////////////////////////////////
   if (compareValue(lastKeyStrokeVal, currMask)) 
   {
      return 0;
   }
   else
   {
      return -1;
   }
} //end scrutinizeKeyVal()


//////////////////////////////////////////////////////////
// processKeyPress(): Check the value of each keystroke //
// as they are typed                                    //
//////////////////////////////////////////////////////////
function processKeyPress(obj) 
{
   var retVal = scrutinizeKeyVal(obj);
   
   if (retVal == -1) // scrutinizeKeyVal returned false: Key value does not match mask //
   {
      return false;
   }
   else if (retVal == 0) // scrutinizeKeyVal returned true: Key value does match mask //
   {
      return true;
   }
   else if (retVal == 1) // scrutinizeKeyVal encountered delimiter character //
   {
      //////////////////////////////////////////////////////
      // This will cancel the current keypress event and  //
      // force the separator char to be appended in field //
      //////////////////////////////////////////////////////
      obj.value = obj.value + lastKeyStrokeVal + sepChar;
      return false;
   }
} //end processKeyPress()


////////////////////////////////////////////////////////
// isValidDate(): Determines if a date value is valid //
// Uses the date format "MM/DD/YYYY"                  //
////////////////////////////////////////////////////////
function isValidDate(obj)       
{
   var s = new String;
   s = obj.value;
   monthVal = s.charAt(0) + s.charAt(1);
   dayVal = s.charAt(3) + s.charAt(4);
   yearVal = s.charAt(6) + s.charAt(7) + s.charAt(8) + s.charAt(9);

   if (parseInt(dayVal) > parseInt(daysInMonth(monthVal)))
   {
      alert("bad date");
      obj.focus();
      obj.select();
      return false;
   }
   return true;
}  //end isValidDate()


/////////////////////////////////////////////
// daysInMonth(): Determines the number of //
// allowable days in a month.              //                              
/////////////////////////////////////////////
function daysInMonth(charMonth)
{
   if ((charMonth == "01") || (charMonth == "03") || (charMonth == "05") 
       || (charMonth == "07") || (charMonth == "08") || (charMonth == "10") 
       || (charMonth == "12"))
      return 31;

   if (charMonth == "02")
   {
      if (isLeapYear(yearVal))
         return 29;
      return 28;
   }

   if ((charMonth == "04") || (charMonth == "06") || (charMonth == "09") 
       || (charMonth == "11"))
      return 30;
}


//////////////////////////////////////////////
// isLeapYear(): Determines if year is leap //
//////////////////////////////////////////////
function isLeapYear(intYear) 
{
   if ((intYear % 100 == 0) && (intYear % 400 == 0))
   {
      return true; 
   }
   else 
   {
      if ((intYear % 4) == 0) 
         return true; 
      return false;
   }
}

////////////////////////////////////////////////////
// clearFields(): Clears/resets all fields on the //
// form to whatever you want                      //
////////////////////////////////////////////////////
function clearFields()
{
   DateField.value = "";
   DateField.focus();
}


//////////////////////////////////////////////////
// compareValue(): Compares a value to its mask //
// (both args are passed in)                    //
//////////////////////////////////////////////////
function compareValue(cmpVal, mask)
{
  if(!cmpVal.match(mask))
  {
     return false;
  }
  else
  {
     return true;
  }
}


/////////////////////////////////////////////////////////
// validateForm() : Validates the form when the Submit //
// button is pressed.                                  //
/////////////////////////////////////////////////////////
function validateForm(obj)
{
   if ((compareValue(DateField.value, fullDateMask)) && isValidDate(obj))
   {
      alert("Good date");
   }
   else
   {
      alert("Bad date");
      DateField.focus();
   }
}