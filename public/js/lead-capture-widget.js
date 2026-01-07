/**
 * Emphxs Lead Capture Widget
 *
 * Usage:
 * 1. Include this script on your page:
 *    <script src="https://your-domain.com/js/lead-capture-widget.js" data-api-url="https://your-api-domain.com"></script>
 *
 * 2. Add a container element where you want the form:
 *    <div id="emphxs-lead-form"></div>
 *
 * 3. Or trigger the modal:
 *    <button onclick="EmphxsLeadWidget.openModal()">Get a Quote</button>
 */

(function() {
  'use strict';

  const WIDGET_VERSION = '1.0.0';

  // Get API URL from script tag or use default
  const scriptTag = document.currentScript || document.querySelector('script[data-api-url]');
  const API_URL = scriptTag?.getAttribute('data-api-url') || window.location.origin;

  const styles = `
    .emphxs-widget-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999999;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
    }
    .emphxs-widget-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    .emphxs-widget-modal {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 520px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      transform: translateY(20px);
      transition: transform 0.3s;
    }
    .emphxs-widget-overlay.active .emphxs-widget-modal {
      transform: translateY(0);
    }
    .emphxs-widget-header {
      padding: 24px 24px 0;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
    .emphxs-widget-title {
      font-size: 24px;
      font-weight: 700;
      color: #1a1a2e;
      margin: 0 0 8px;
    }
    .emphxs-widget-subtitle {
      font-size: 14px;
      color: #6b7280;
      margin: 0;
    }
    .emphxs-widget-close {
      background: #f3f4f6;
      border: none;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
    }
    .emphxs-widget-close:hover {
      background: #e5e7eb;
    }
    .emphxs-widget-body {
      padding: 24px;
    }
    .emphxs-widget-form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .emphxs-widget-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    @media (max-width: 480px) {
      .emphxs-widget-row {
        grid-template-columns: 1fr;
      }
    }
    .emphxs-widget-field {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .emphxs-widget-label {
      font-size: 13px;
      font-weight: 500;
      color: #374151;
    }
    .emphxs-widget-required {
      color: #ef4444;
    }
    .emphxs-widget-input,
    .emphxs-widget-select,
    .emphxs-widget-textarea {
      padding: 10px 14px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
      font-family: inherit;
    }
    .emphxs-widget-input:focus,
    .emphxs-widget-select:focus,
    .emphxs-widget-textarea:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .emphxs-widget-input.error,
    .emphxs-widget-select.error,
    .emphxs-widget-textarea.error {
      border-color: #ef4444;
    }
    .emphxs-widget-error {
      color: #ef4444;
      font-size: 12px;
    }
    .emphxs-widget-textarea {
      resize: vertical;
      min-height: 100px;
    }
    .emphxs-widget-submit {
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
      color: white;
      border: none;
      padding: 14px 24px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      margin-top: 8px;
    }
    .emphxs-widget-submit:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }
    .emphxs-widget-submit:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    .emphxs-widget-success {
      text-align: center;
      padding: 40px 24px;
    }
    .emphxs-widget-success-icon {
      width: 64px;
      height: 64px;
      background: #10b981;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }
    .emphxs-widget-success h3 {
      font-size: 20px;
      font-weight: 600;
      color: #1a1a2e;
      margin: 0 0 8px;
    }
    .emphxs-widget-success p {
      color: #6b7280;
      margin: 0;
    }
    .emphxs-widget-inline {
      background: white;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .emphxs-widget-inline .emphxs-widget-title {
      font-size: 20px;
    }
    /* Honeypot field - hidden from users but visible to bots */
    .emphxs-hp-field {
      position: absolute;
      left: -9999px;
      top: -9999px;
    }
  `;

  const serviceInterests = {
    web_development: 'Web Development',
    mobile_app: 'Mobile App Development',
    software_development: 'Software Development',
    ui_ux_design: 'UI/UX Design',
    ecommerce: 'E-Commerce Solutions',
    maintenance: 'Website Maintenance',
    consulting: 'IT Consulting',
    other: 'Other Services'
  };

  const budgetRanges = {
    under_500: 'Under $500',
    '500_1000': '$500 - $1,000',
    '1000_5000': '$1,000 - $5,000',
    '5000_10000': '$5,000 - $10,000',
    '10000_plus': '$10,000+',
    not_sure: 'Not Sure Yet'
  };

  const timelines = {
    immediate: 'Immediately',
    '1_month': 'Within 1 Month',
    '1_3_months': '1-3 Months',
    '3_6_months': '3-6 Months',
    flexible: 'Flexible'
  };

  function injectStyles() {
    if (document.getElementById('emphxs-widget-styles')) return;
    const styleEl = document.createElement('style');
    styleEl.id = 'emphxs-widget-styles';
    styleEl.textContent = styles;
    document.head.appendChild(styleEl);
  }

  function createFormHTML(isModal = false) {
    return `
      <div class="${isModal ? 'emphxs-widget-header' : ''}">
        <div>
          <h2 class="emphxs-widget-title">Get a Free Quote</h2>
          <p class="emphxs-widget-subtitle">Tell us about your project and we'll get back to you within 24 hours.</p>
        </div>
        ${isModal ? `
          <button class="emphxs-widget-close" onclick="EmphxsLeadWidget.closeModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        ` : ''}
      </div>
      <div class="emphxs-widget-body">
        <form class="emphxs-widget-form" id="emphxs-lead-form-element">
          <div class="emphxs-widget-row">
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">First Name <span class="emphxs-widget-required">*</span></label>
              <input type="text" name="first_name" class="emphxs-widget-input" required>
            </div>
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Last Name</label>
              <input type="text" name="last_name" class="emphxs-widget-input">
            </div>
          </div>

          <div class="emphxs-widget-row">
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Email <span class="emphxs-widget-required">*</span></label>
              <input type="email" name="email" class="emphxs-widget-input" required>
            </div>
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Phone</label>
              <input type="tel" name="phone" class="emphxs-widget-input">
            </div>
          </div>

          <div class="emphxs-widget-row">
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Company</label>
              <input type="text" name="company_name" class="emphxs-widget-input">
            </div>
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Service Interest <span class="emphxs-widget-required">*</span></label>
              <select name="service_interest" class="emphxs-widget-select" required>
                ${Object.entries(serviceInterests).map(([key, label]) =>
                  `<option value="${key}">${label}</option>`
                ).join('')}
              </select>
            </div>
          </div>

          <div class="emphxs-widget-row">
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Budget Range</label>
              <select name="budget_range" class="emphxs-widget-select">
                <option value="">Select budget...</option>
                ${Object.entries(budgetRanges).map(([key, label]) =>
                  `<option value="${key}">${label}</option>`
                ).join('')}
              </select>
            </div>
            <div class="emphxs-widget-field">
              <label class="emphxs-widget-label">Timeline</label>
              <select name="timeline" class="emphxs-widget-select">
                <option value="">Select timeline...</option>
                ${Object.entries(timelines).map(([key, label]) =>
                  `<option value="${key}">${label}</option>`
                ).join('')}
              </select>
            </div>
          </div>

          <div class="emphxs-widget-field">
            <label class="emphxs-widget-label">Project Description <span class="emphxs-widget-required">*</span></label>
            <textarea name="project_description" class="emphxs-widget-textarea" required placeholder="Tell us about your project, goals, and any specific requirements..."></textarea>
          </div>

          <!-- Honeypot field for spam protection -->
          <div class="emphxs-hp-field">
            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
          </div>

          <!-- Hidden UTM fields -->
          <input type="hidden" name="utm_source" value="">
          <input type="hidden" name="utm_medium" value="">
          <input type="hidden" name="utm_campaign" value="">
          <input type="hidden" name="utm_content" value="">
          <input type="hidden" name="utm_term" value="">
          <input type="hidden" name="landing_page" value="">
          <input type="hidden" name="referrer_url" value="">

          <button type="submit" class="emphxs-widget-submit">
            Send Request
          </button>
        </form>
      </div>
    `;
  }

  function createSuccessHTML() {
    return `
      <div class="emphxs-widget-success">
        <div class="emphxs-widget-success-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
            <path d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h3>Thank You!</h3>
        <p>We've received your inquiry and will contact you within 24 hours.</p>
      </div>
    `;
  }

  function getUTMParams() {
    const params = new URLSearchParams(window.location.search);
    return {
      utm_source: params.get('utm_source') || '',
      utm_medium: params.get('utm_medium') || '',
      utm_campaign: params.get('utm_campaign') || '',
      utm_content: params.get('utm_content') || '',
      utm_term: params.get('utm_term') || '',
      landing_page: window.location.href,
      referrer_url: document.referrer || ''
    };
  }

  function fillUTMFields(form) {
    const utm = getUTMParams();
    Object.entries(utm).forEach(([key, value]) => {
      const input = form.querySelector(`input[name="${key}"]`);
      if (input) input.value = value;
    });
  }

  async function submitForm(form, container) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    const submitBtn = form.querySelector('.emphxs-widget-submit');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    try {
      const response = await fetch(`${API_URL}/api/public/leads`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (response.ok && result.success) {
        container.innerHTML = createSuccessHTML();
      } else {
        throw new Error(result.message || 'Failed to submit');
      }
    } catch (error) {
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
      alert('Error: ' + (error.message || 'Failed to submit. Please try again.'));
    }
  }

  // Public API
  window.EmphxsLeadWidget = {
    version: WIDGET_VERSION,

    init: function(options = {}) {
      injectStyles();

      // Initialize inline form if container exists
      const inlineContainer = document.getElementById('emphxs-lead-form');
      if (inlineContainer) {
        this.renderInline(inlineContainer);
      }

      // Create modal container
      if (!document.getElementById('emphxs-widget-overlay')) {
        const overlay = document.createElement('div');
        overlay.id = 'emphxs-widget-overlay';
        overlay.className = 'emphxs-widget-overlay';
        overlay.innerHTML = `<div class="emphxs-widget-modal">${createFormHTML(true)}</div>`;
        overlay.addEventListener('click', (e) => {
          if (e.target === overlay) this.closeModal();
        });
        document.body.appendChild(overlay);

        const modalForm = overlay.querySelector('#emphxs-lead-form-element');
        if (modalForm) {
          fillUTMFields(modalForm);
          modalForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitForm(modalForm, overlay.querySelector('.emphxs-widget-modal'));
          });
        }
      }
    },

    renderInline: function(container) {
      container.className = 'emphxs-widget-inline';
      container.innerHTML = createFormHTML(false);

      const form = container.querySelector('#emphxs-lead-form-element');
      if (form) {
        fillUTMFields(form);
        form.addEventListener('submit', (e) => {
          e.preventDefault();
          submitForm(form, container);
        });
      }
    },

    openModal: function() {
      const overlay = document.getElementById('emphxs-widget-overlay');
      if (overlay) {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    },

    closeModal: function() {
      const overlay = document.getElementById('emphxs-widget-overlay');
      if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      }
    }
  };

  // Auto-initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => EmphxsLeadWidget.init());
  } else {
    EmphxsLeadWidget.init();
  }
})();
