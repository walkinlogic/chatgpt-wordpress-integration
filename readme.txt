=== ChatGPT Integration ===
Contributors: M Haroon Abbas
Tags: chatgpt, ai, chatbot
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0

== Description ==

Integrate ChatGPT with your WordPress site using a Python backend server.

== Installation ==

1. Set up the Python server (see instructions in python-server/README.txt)
2. Upload the plugin folder to your /wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the API settings in Settings -> ChatGPT
5. Use the [chatgpt] shortcode in your posts/pages

== Shortcode Parameters ==

[chatgpt]
[chatgpt model="gpt-4"]
[chatgpt 
    placeholder="Your prompt text..." 
    button_text="Ask" 
    max_tokens="150" 
    temperature="0.7" 
    model="gpt-3.5-turbo"
]

== Changelog ==

= 1.0 =
* Initial release

== Python Server Setup ==

Install Python 3.7+ if not already installed

Create a new directory for the Python server

Add the three Python files (chatgpt_api.py, requirements.txt, .env.example)

Rename .env.example to .env and fill in your OpenAI API key

Install dependencies:
    pip install -r requirements.txt

Run the server:
    python chatgpt_api.py

     