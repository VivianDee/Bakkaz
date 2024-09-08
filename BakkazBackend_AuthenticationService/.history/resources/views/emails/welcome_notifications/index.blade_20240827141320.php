@php
    use Carbon\Carbon;

    // Get the current time
    $now = Carbon::now();

    // Add time (e.g., 5 days, 3 hours)
    $newTime = $now->addDays(5)->addHours(3);

    // Format the time as you like (e.g., '12 Nov, 2021 - H:i')
    $formattedTime = $newTime->format('d M, Y');
@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Welcome to RecenthPost</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    />
    <style>
        .next__option {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            text-align: left;
        }
        .next__option p {
            font-size: 18px;
            font-weight: 600;
            color: #1f1f1f;
        }
        .next__option ol {
            padding-left: 20px;
        }
        .next__option .head {
            font-weight: 600;
            color: #ba3d4f;
        }
        .help__sec {
            margin-top: 40px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            text-align: center;
        }
        .help__sec .help {
            font-size: 18px;
            font-weight: 600;
            color: #1f1f1f;
        }
        .help__sec a {
            color: #c93b4d;
            text-decoration: none;
        }
        .help__sec span {
            color: #c93b4d;
        }
    </style>
</head>
<body
    style="
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #ffffff;
      font-size: 14px;
    "
>
<div
    style="
        max-width: 680px;
        margin: 0 auto;
        padding: 45px 30px 60px;
        background-size: 800px 452px;
        background: #f4f7ff url('https://res.cloudinary.com/djhx4hfer/image/upload/v1721896327/headerbg_rungij.png') no-repeat top center;
        font-size: 14px;
        color: #434343;
      "
>
    <header>
        <table style="width: 100%;">
            <tbody>
            <tr style="height: 0;">
                <td>
                    <img
                        alt=""
                        src="https://res.cloudinary.com/djhx4hfer/image/upload/v1723566342/RP_WHITEB_LOGO_b9b6qu.png"
                        height="30px"
                    />
{{--                </td>--}}
{{--                <td style="text-align: right;">--}}
{{--                <span--}}
{{--                    style="font-size: 16px; line-height: 30px; color: #ffffff;"--}}
{{--                >{{$formattedTime}}</span--}}
{{--                >--}}
{{--                </td>--}}
            </tr>
            </tbody>
        </table>
    </header>

    <main>
        <div
            style="
            margin: 0;
            margin-top: 70px;
            padding: 92px 30px 115px;
            background: #ffffff;
            border-radius: 30px;
            text-align: center;
          "
        >
            <div style="width: 100%; max-width: 489px; margin: 0 auto;">
                <h1
                    style="
                margin: 0;
                font-size: 24px;
                font-weight: 500;
                color: #1f1f1f;
              "
                >
                    Welcome to RecenthPost!
                </h1>
                <p
                    style="
                margin: 0;
                margin-top: 17px;
                font-size: 16px;
                font-weight: 500;
              "
                >
                    Hey {{$name}},
                </p>
                <p
                    style="
                margin: 0;
                margin-top: 17px;
                font-weight: 500;
                letter-spacing: 0.56px;
                font-size:16px
              "
                >
                    We're thrilled to have you with us! At RecenthPost, we're committed to delivering the latest information and insights tailored just for you.
                </p>
                <p
                    style="
                margin: 0;
                margin-top: 20px;
                font-size: 16px;
                font-weight: 500;
              "
                >
                    If you have any questions or need assistance, feel free to reach out to our support team.
                </p>
            </div>
        </div>

        <div class="next__option">
            <p>What can you do next?</p>
            <ol>
                <li><span class="head">Explore Features:</span> Discover the various features designed to enhance your experience.</li>
                <li><span class="head">Personalize Your Profile:</span> Update your profile with your information to get a personalized experience.</li>
                <li><span class="head">Verify Your Account:</span> Verify your account to access all the features and benefits of RecenthPost. Reference Settings.</li>
                <li><span class="head">Get Started Guide:</span> Check out our <span>support resources</span> to help you navigate through our app and make the most out of it.</li>
            </ol>
        </div>

        <div class="help__sec">
            <p class="help">Need help?</p>
            <p>Our support team is here to assist you. If you have any questions or need help, feel free to contact us at <a href="mailto:support@recenthpost.com">support@recenthpost.com</a> or visit our <span>Help Center</span>.</p>
            <p>Thank you for choosing RecenthPost. We look forward to serving you!</p>
            <p>Best regards, The RecenthPost Team.</p>
        </div>

        <p
            style="
            max-width: 400px;
            margin: 0 auto;
            margin-top: 90px;
            text-align: center;
            font-weight: 500;
            color: #8c8c8c;
          "
        >
            Need more help? Ask at
            <a
                href="mailto:support@recenthpost.com"
                style="color: #c93b4d; text-decoration: none;"
            >support@recenthpost.com</a
            >
            or visit our
            <a
                href="http://recenthpost.com"
                target="_blank"
                style="color: #c93b4d; text-decoration: none;"
            >Help Center</a
            >
        </p>
    </main>

    <footer
        style="
          width: 100%;
          max-width: 490px;
          margin: 20px auto 0;
          text-align: center;
          border-top: 1px solid #e6ebf1;
        "
    >
        <p
            style="
            margin: 0;
            margin-top: 40px;
            font-size: 16px;
            font-weight: 600;
            color: #434343;
          "
        >
            RecenthPost
        </p>
        <p style="margin: 0; margin-top: 16px; color: #434343;">
            Copyright © 2024 Company. All rights reserved.
        </p>
    </footer>
</div>
</body>
</html>
