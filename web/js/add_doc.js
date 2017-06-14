var pdf = document.getElementById('conserto_kiosquebundle_magazine_pdf_Pdf');
var Title = document.getElementById('conserto_kiosquebundle_magazine_Title');
(function(){
    // gestionnaires
    function initHandlers() {
        pdf.addEventListener('change', onFileChange, false);
    }
    function onFileChange(event) {
      event.stopPropagation();
      var title = pdf.files[0].name;
      title = title.slice(0,-4);
      title = title.replace("-", " ");
      title = title.replace("_", " ");
      Title.value = title;
      event.preventDefault();
    }
    initHandlers();
})();
