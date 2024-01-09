
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Online Speech Detection and Transcription Tool</title>

    
      
      
        <style>
          html {
            font-size: 10px;
          }
      
          body {
            background:#ffc600;
            font-family: 'helvetica neue';
            font-weight: 200;
            font-size: 20px;
          }
      
          .words {
            max-width:500px;
            margin:50px auto;
            background:white;
            border-radius:5px;
            box-shadow:10px 10px 0 rgba(0,0,0,0.1);
            padding:1rem 2rem 1rem 5rem;
            background: -webkit-gradient(linear, 0 0, 0 100%, from(#d9eaf3), color-stop(4%, #fff)) 0 4px;
            background-size: 100% 3rem;
            position: relative;
            line-height:3rem;
          }
          p {
            margin: 0 0 3rem 0;
          }
      
          .words:before {
            content: '';
            position: absolute;
            width: 4px;
            top: 0;
            left: 30px;
            bottom: 0;
            border: 1px solid;
            border-color: transparent #efe4e4;
          }
      
      .languages button.active {background-color: #BADA55;}
      
          div.sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        padding: 5px;
       
      }
          
        </style>

  </head>
  <body>
  <div class="languages sticky">
    <button type="button" data-language="no">Norwegian</button>
    <button type="button" class="active" data-language="en-US">English (US)</button>
<br/>

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Speech recognition -->
<ins class="adsbygoogle"
     style="display:inline-block;width:320px;height:100px"
     data-ad-client="ca-pub-3671671468926837"
     data-ad-slot="3447492903"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
   </div>
   
    <div class="words" contenteditable>
     
    </div>
  
    <script>
      
      //todo localstorage av språk
      //Modified tutorial from Javascript30.com
      
        window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const recognition = new SpeechRecognition();
      recognition.interimResults = true;
      recognition.lang = 'en-US';
      
      const languageButtons = document.querySelectorAll('[data-language]'); 
      languageButtons.forEach(language => language.addEventListener('click', setLanguage));
      
      const userLanguage = window.navigator.language;
      let selectedLanguage = '';
      
      let p = document.createElement('p'); 
      const words = document.querySelector('.words');
      words.appendChild(p);
      
      recognition.addEventListener('result', e =>{
        //console.log(e.results);
        const transcript = Array.from(e.results)
          .map(result => result[0])
          .map(result => result.transcript)
          .join('')
       
          p.textContent = transcript;
          if(e.results[0].isFinal){
              p = document.createElement('p');
              words.appendChild(p);
              window.scrollTo(0,document.body.scrollHeight); //scroller ned
          }
      
          if(transcript.includes('unicorn')){
              console.log('Unicorn');
          }
          console.log(transcript);
      });
      
      
      
      
      
      
      
      function setLanguage(e) {  
          
          selectedLanguage = this.dataset.language;
          recognition.lang = selectedLanguage;
          console.log('Selected language' + selectedLanguage);
          languageButtons.forEach(language => language.classList.remove('active'));
          this.classList.add('active'); 
          return;
      }
      
      recognition.addEventListener('end', recognition.start)
      
      recognition.start();
      
      
      </script>
  
  </body>
  </html>
  