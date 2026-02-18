# Clusterify.AI ChatBot & Assistant for Magento 2

** This is a pre version and the first release will come in weeks. All feedbacks are welcome! **

## Introduction

**Clusterify.AI** is an advanced AI-powered assistant platform designed to enhance customer engagement and support on your e-commerce store. This module integrates the Clusterify.AI ChatBot into your Magento 2 store, allowing you to deploy an intelligent virtual assistant that can handle customer inquiries, product recommendations, and support tickets 24/7.

The **Clusterify.AI ChatBot Magento 2 Module** provides a seamless connection between your Magento store and the Clusterify.AI platform. It injects the necessary chatbot script into your storefront pages with granular control over visibility and placement.

## Requirements

- **Magento Open Source / Adobe Commerce:** 2.3.x, 2.4.x
- **PHP:** 7.3, 7.4, 8.1, 8.2, 8.3 (Compatible with your Magento version's requirements)
- **Clusterify.AI Account:** You must have an active account and a configured ChatBot/Assistant on [Clusterify.AI Dashboard](https://dashboard.clusterify.ai).

## Installation

### Option 1: Composer Installation (Recommended)

If the package is available via Composer:

```bash
composer require clusterifyai/module-chatbot
bin/magento module:enable ClusterifyAI_Chatbot
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

### Option 2: Manual Installation via ZIP

1.  **Download** the latest release from the [GitHub Repository](https://github.com/ClusterifyAI/magento2-chatbot-module).
2.  **Extract** the contents of the ZIP file.
3.  **Upload** the files to your Magento root directory:
    `app/code/ClusterifyAI/Chatbot/`
4.  **Run the following commands** in your terminal:

```bash
bin/magento module:enable ClusterifyAI_Chatbot
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

## Setup & specific Configuration

### Deployment Safety & Staged Rollout

> [!NOTE]
> **Safe to Install:** This module is **disabled by default**. Installation and deployment will **not** immediately affect your storefront. You can safely configure your credentials and verify the API connection using the built-in "Test Connection" tool before enabling the chatbot for your customers.

To configure the module:

1.  Log in to your **Magento Admin Panel**.
2.  Navigate to **Stores > Configuration**.
3.  In the left sidebar, expand **Clusterify.AI**.
4.  **Configure API Access:**
    - Select **API Access** on the left hand side menu.
    - Enter your **API Base URL** (e.g., `https://dashboard.clusterify.ai/api`).
    - Enter your **Public Key** and **Secret Key**.
    - Click **TEST API Login** to verify your credentials.
    - **Save Config**.
5.  **Configure ChatBot Connection:**
    - Select **ChatBot** on the left hand side menu.
    - Expand the **Connection** section.
    - Enter your **Chatbot UUID**. (Found in your Clusterify.AI Dashboard settings).
    - Click **Test Connection** to verify the chatbot is accessible.
6.  **Configure Visibility:**
    - Expand **Display Settings**.
    - Choose exactly which pages the chatbot should appear on (Home, Product, Checkout, etc.). Recommended to allow the chatbot only one page type first to test. Then open the rest of them.
7.  **Go Live:**
    - Expand the **General** section.
    - Set **Is Enabled** to **Yes**.
8.  **Save Config**.
    - Flush the cache if necessary. The chatbot will now be visible on the store front.
    - Go to one of the selected website page and check the chatbot at the right-bottom.

### Display Settings

You have full control over where the chatbot appears on your site. Under the **Display Settings** group, you can toggle visibility for specific page types:

- **Home Page**: Show/Hide on the homepage.
- **CMS Pages**: Show/Hide on standard CMS pages.
- **Category Pages**: Show/Hide on product listing pages.
- **Product Pages**: Show/Hide on product detail pages.
- **Checkout Pages**: Show/Hide on the checkout timeline.
- **Shopping Cart**: Show/Hide on the cart page.
- **My Account**: Show/Hide on customer dashboard pages.
- **Login / Register / Forgot Password**: Control visibility on authentication pages.
- **Search / Advanced Search**: Show/Hide on search result pages.
- **Contact Us**: Show/Hide on the contact page.
- **Compare / Wishlist**: Control visibility on these utility pages.
- **Other Pages**: Fallback for any page not covered by the types above.

## Technical Description

The module is designed to be lightweight and performance-friendly.

### Architecture

- **Frontend Injection:** The module uses a standard Magento Block (`ClusterifyAI\Chatbot\Block\ChatBot`) and Template (`view/frontend/templates/chatbot.phtml`) to render the integration script.
- **Layout XML:** The block is injected via `view/frontend/layout/default.xml` into the `before.body.end` container, ensuring it loads at the end of the page body.
- **Vanilla JavaScript:** The loader script uses pure Vanilla JS. **No jQuery or RequireJS dependencies are used.** This ensures maximum compatibility with all Magento themes, including Luma and Hyvä.
- **Configuration Scope:** All settings are stored in `core_config_data` and support Store View scope, allowing you to use different chatbots for different store views (e.g., different languages).

### Hyvä Theme Compatibility

✅ **Fully Compatible.**
Since the module relies on standard layout XML and Vanilla JavaScript without any dependency on RequireJS, Knockout, or jQuery, it works out-of-the-box with the Hyvä Theme.

## License

This project is licensed under the [MIT License](LICENSE).

## Support

If you encounter any issues or have questions, please contact our support team at [support@clusterify.ai](mailto:support@clusterify.ai) or satisfy a ticket in your Clusterify.AI dashboard.

## Frequently Asked Questions (FAQ)

**Q: Does this module slow down my website?**
A: No. The chatbot script is loaded asynchronously, meaning it does not block the rendering of your page. Your critical rendering path remains unaffected.

**Q: Can I customize the look and feel of the ChatBot?**
A: Yes. The appearance of the chatbot (colors, icons, welcome messages) is controlled directly from your Clusterify.AI Dashboard. Changes made there are instantly reflected on your Magento store without needing to update this module.

**Q: Configuring specific pages for the ChatBot doesn't seem to work.**
A: Ensure that you have cleared your Magento Cache (`System > Cache Management`) after saving configuration changes. If you use a Full Page Cache (FPC) like Varnish, you may need to flush it as well.

**Q: Is this module compatible with Hyvä?**
A: Yes, it is fully compatible with Hyvä out of the box.

**Q: How do I find my Chatbot UUID?**
A: Log in to your Clusterify.AI account, select your ChatBot project, and go to the "Integrations" or "Settings" tab. Copy the UUID provided there.

**Q: Does the module disable page caching?**
A: No. The module is fully compatible with Magento's standard Full Page Cache (FPC) and Varnish. It uses standard layout injection and does not force any page to be uncacheable.
