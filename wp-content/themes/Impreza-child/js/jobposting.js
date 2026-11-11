jQuery( document ).ready(function() {
    var dataRows = [];
    jQuery('#job-openings .w-grid-list article').each(function(){
        var rowJob = {
            "@context": "http://www.schema.org",
            "@type": "JobPosting",
            "title": jQuery(this).find('h3 a').text(),
            "hiringOrganization": {
                "@type": "Organization",
                "name": "Living Branches"
            }
        }
        dataRows.push(rowJob);
    });
     var script = document.createElement('script');
     script.type = "application/ld+json";
     script.innerHTML = JSON.stringify(dataRows);
     document.getElementsByTagName('head')[0].appendChild(script);
});