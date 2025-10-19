import { initializeApp } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-analytics.js";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-auth.js";

// Firebase config from Firebase Console
  const firebaseConfig = {
    apiKey: "AIzaSyBVf1deZy4LKvgEQtBRUCj9UUg8T9GugDA",
    authDomain: "findatutor-1a3f8.firebaseapp.com",
    projectId: "findatutor-1a3f8",
    storageBucket: "findatutor-1a3f8.firebasestorage.app",
    messagingSenderId: "1080357293923",
    appId: "1:1080357293923:web:763e921107196fd9b28d56",
    measurementId: "G-67Y55NB8SZ"
  };

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

document.getElementById("googleSignIn").addEventListener("click", () => {
  signInWithPopup(auth, provider)
    .then((result) => {
      const user = result.user;
      console.log("User Info:", user);

      // PHP to backend 
      fetch("google_signup.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: user.displayName,
          email: user.email,
          firebase_uid: user.uid
        })
      }).then(res => res.text())
        .then(data => {
          alert("Signed up with Google: " + data);
          window.location.href = "profile.php";
        });

    })
    .catch((error) => {
      console.error("Google Sign-In Error:", error.message);
    });
});