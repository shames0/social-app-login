function sal_onsuccess_google (googleUser) {
    var profile = googleUser.getBasicProfile();
    console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
    console.log('Name: ' + profile.getName());
    console.log('Image URL: ' + profile.getImageUrl());
    console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
}

function sal_onfailure_google () {
    console.log('ouch!');
}


function renderButton () {
    gapi.signin2.render('my-signin', {
        'scope': 'profile email',
        'width': 270,
        'height': 50,
        'longtitle': true,
        'theme': 'light',
        'onsuccess': sal_onsuccess_google,
        'onfailure': sal_onfailure_google,
    });
}
