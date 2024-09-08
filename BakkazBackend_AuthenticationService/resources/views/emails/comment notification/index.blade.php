<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/assets/svgs/logo.svg" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0"
    />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@100;200;300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <title>Comment Notification</title>
    <style>
      body {
        font-family: "Poppins", sans-serif;
        margin: 0;
        padding: 0;
        background-color: #fef8f8;
      }
      .header {
        position: relative;
        text-align: center;
      }
      .header__background {
        position: relative;
      }
      .header__image {
        width: 100%;
        height: auto;
      }
      .header__content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-direction: row;
        justify-content: center;
      }
      .header__logo {
        display: block;
        margin: 0 auto;
        width: 40px; /* Adjusted for smaller screens */
        height: auto;
      }
      .header__title {
        text-transform: capitalize;
        font-size: 24px; /* Adjusted for smaller screens */
        line-height: 24px;
        color: white;
      }
      /* the section div */
      .content-sec{
       display: flex;
       align-items: center;
       justify-content: center;

      }
      .content {
        text-align: center;
        padding: 20px;
        width: 550px;
      }
      .content h4 {
        font-size: 24px;
        margin: 57px 0;
      }

      .comment-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 600px;
        padding: 20px;
      }

      .comment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-right: 10px;
      }

      .user-details {
        flex-grow: 1;
        text-align: left;
      }

      .username {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
      }

      .title {
        margin: 0;
        text-align: left;
        text-transform: capitalize;
        font-size: 14px;
        font-weight: 400;
        color: #6941C6;
        background-color: #F9F5FF;
        border-radius: 16px;
        padding: 2px 10px;
        width: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .timestamp {
        font-size: 12px;
        color: gray;
      }

      .comment-text {
        text-align: left;
        margin-top: 10px;
      }

      .stats__flag {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
      }

      .stats {
        display: flex;
      }

      .stat {
        display: flex;
        align-items: center;
        margin-right: 10px;
      }

      .stat img {
        margin-right: 5px;
      }

      .flag_share_dot img {
        margin-left: 10px;
      }
      .view-comment-btn {
        background-color: #C93B4D;
        border: none;
        border-radius: 50px;
        color: white;
        cursor: pointer;
        display: inline-block;
        font-size: 16px;
        margin-top: 90px;
        padding: 10px 24px;
        height: 48px;
        text-decoration: none;
      }

      .view-comment-btn a {
        color: white;
        text-decoration: none;
      }

      /* socials */
     
      .socials {
        text-align: left;
        margin-top: 50px ;
        
        
      }
      .socials > img {
        width: 24px;
        height: 24px;
        margin: 0 10px;
      }
      /* no reply section */
      .noreply {
        font-style: italic;
        text-align: center;
        font-size: 10px;
        margin-top:70px;
      }
      /* footer section */
      .footer {
        background-color: #efefef;
        padding: 20px 16px;
        text-align: center;
      }
      .footer h5 {
        font-size: 20px;
        text-transform: capitalize;
      }
      .footer p {
        font-size: 14px;
      }
      .footer .download__logos {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 30px;
        justify-content: center;
        margin: 20px 0;
        text-transform: capitalize;
      }
      .footer .download__logos .google-sec,
      .footer .download__logos .apple-sec {
        display: flex;
        align-items: center;
        margin: 0 10px;
        background-color: #0b0b0b;
        border-radius: 8px;
        color: white;
        justify-content: center;
        gap: 10px;
        width: 200px;
      }
      .apple,
      .google {
        font-weight: 700;
        font-size: 20px;
      }
      .footer .download__logos img {
        width: 50px;
        height: auto;
        margin-right: 10px;
      }
      .footer .support__center a {
        color: #3771c8;
        text-decoration: none;
      }

      .footer .address__copyright {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
      }
      .footer .address a {
        margin-bottom: 10px;
        color: #232323;
        text-decoration: none;
      }

      .footer .copyright {
        font-size: 14px;
        color: #3771c8;
      }

      @media (min-width: 768px) {
        .header__content {
          flex-direction: row;
          justify-content: center;
        }
        .header__logo {
          width: 50px; /* Adjusted for medium screens */
        }
        .header__title {
          font-size: 32px; /* Adjusted for medium screens */
          line-height: 32px;
        }
        .footer .download__logos {
          display: flex;
          flex-direction: row;
          /* gap: 10px; */
        }
      }
      @media (min-width: 1024px) {
        .header__logo {
          width: 60px; /* Adjusted for larger screens */
        }
        .header__title {
          font-size: 64px; /* Adjusted for larger screens */
          line-height: 64px;
        }
        .footer .download__logos {
          display: flex;
          flex-direction: row;
          /* gap: 10px; */
        }
      }
    </style>
  </head>
  <body>
    
    <header class="header">
      <div class="header__background">
        <img
          src="./assets/svgs/headerbg.png"
          alt="header background"
          class="header__image"
        />
        <div class="header__content">
          <img src="./assets/svgs/logo.svg" alt="logo" class="header__logo" />
          <img src="./assets/svgs/line.svg" alt="line" class="header__line" />
          <h1 class="header__title">recenthpost</h1>
        </div>
      </div>
    </header>
    <div class="content-sec">
    <section class="content">
      <h4>User ### added a comment to your post</h4>
      <div class="comment-card">
        <div class="comment-content">
          <div class="comment-header">
            <img
              src="./assets/comments/jane.svg"
              alt="User Avatar"
              class="avatar"
            />
            <div class="user-details">
              <h5 class="username">Jane Cooper @PR9343839</h5>
             
            </div>
            <span class="timestamp">3h ago</span>
          </div>
          <h6 class="title">politics</h6>

          <p class="comment-text">
            In an inspiring display of unity and generosity, our local community
            recently joined forces for a record-breaking charity event...
          </p>

          <div class="stats__flag">
            <div class="stats">
              <div class="stat">
                <img src="./assets/comments/eye.svg" alt="Views" />
                <p>20k</p>
              </div>
              <div class="stat">
                <img src="./assets/comments/star.svg" alt="Favorites" />
                <p>4k</p>
              </div>
              <div class="stat">
                <img src="./assets/comments/chat.svg" alt="Comments" />
                <p>5k</p>
              </div>
            </div>
            <div class="flag_share_dot">
              <img src="./assets/comments/flag.svg" alt="Flag" />
              <img src="./assets/comments/share.svg" alt="Share" />
              <img src="./assets/comments/dottedline.svg" alt="More" />
            </div>
          </div>
        </div>
      </div>
      <button class="view-comment-btn"><a href="#">View comment</a></button>
      
      <div class="socials">
        <img src="./assets/socials/linkedin.svg" alt="LinkedIn" />
        <img src="./assets/socials/facebook.svg" alt="Facebook" />
        <img src="./assets/socials/instagram.svg" alt="Instagram" />
        <img src="./assets/socials/twitter.svg" alt="Twitter" />
      </div>
   
    </section>
</div>

    <p class="noreply">
      This is an automated message, please do not reply to this email.
    </p>
    <footer class="footer">
      <div>
        <h5>download our app</h5>
        <div class="download__logos">
          <div class="google-sec">
            <img src="./assets/socials/google.png" alt="google-logo" />
            <p>
              get it on <br />
              <span class="google">google play</span>
            </p>
          </div>
          <div class="apple-sec">
            <img src="./assets/socials/apple.png" alt="apple" />
            <p>
              download it on <br />
              <span class="apple">apple store</span>
            </p>
          </div>
        </div>
        <p class="support__center">
          Need help? Visit our
          <span> <a href="mailto:support@bakkaz.com"> support center</a></span>
          or call us on:
          <span><a href="tel:++2347049917629">+2347049917629</a></span>
        </p>
      </div>
      <hr />
      <div class="address__copyright">
        <p class="address">
          <a
            href="https://www.google.com/maps/search/?api=1&query=Plot+B,+Block+H,+Elephant+Cement+Way,+Alausa,+Ikeja,+Lagos+State,+Nigeria"
            target="_blank"
            rel="noopener noreferrer"
          >
            Plot B, Block H, Elephant Cement Way, Alausa, Ikeja, Lagos State,
            Nigeria.
          </a>
        </p>
        <p class="copyright">
          Copyright of recenthpost team &copy;
          <script>
            document.write(new Date().getFullYear());
          </script>
        </p>
      </div>
    </footer>
  </body>
</html>