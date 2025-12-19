(function () {
  "use strict";

  const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
  const { decodeEntities } = window.wp.htmlEntities;
  const { getSetting } = window.wc.wcSettings;
  const { createElement } = window.wp.element;

  // All RaPay gateway IDs
  const rapayGateways = [
    "bank_bni",
    "bank_bca",
    "bank_bri",
    "bank_mandiri",
    "bank_jago",
    "bank_cimb_niaga",
    "bank_citibank",
    "bank_digibank",
    "bank_hsbc",
    "bank_jenius",
    "bank_neo_commerce",
    "bank_danamon",
    "bank_btn",
    "bank_bsi",
    "bank_permata",
    "bank_ocbc_nisp",
    "bank_muamalat",
    "bank_tmrw",
    "bank_line_bank",
    "bank_seabank",
    "bank_allo_bank",
    "bank_krom",
    "gopay",
    "ovo",
    "dana",
    "linkaja",
    "shopeepay",
    "qris",
  ];

  /**
   * Create and register a payment method for a gateway
   */
  const registerRaPayGateway = (gatewayId) => {
    const settings = getSetting(gatewayId + "_data", null);

    if (!settings) {
      return;
    }

    const title = decodeEntities(settings.title) || gatewayId;
    const description = decodeEntities(settings.description || "");
    const icon = settings.icon || "";

    // Label component
    const Label = (props) => {
      const { PaymentMethodLabel } = props.components;

      if (icon) {
        return createElement(
          "span",
          { style: { display: "flex", alignItems: "center", gap: "8px" } },
          createElement("img", {
            src: icon,
            alt: title,
            style: { maxHeight: "24px" },
          }),
          title
        );
      }

      return createElement(PaymentMethodLabel, { text: title });
    };

    // Content component
    const Content = () => {
      return createElement("div", null, description);
    };

    // Register the payment method
    registerPaymentMethod({
      name: gatewayId,
      label: createElement(Label, null),
      content: createElement(Content, null),
      edit: createElement(Content, null),
      canMakePayment: () => true,
      ariaLabel: title,
      supports: {
        features: settings.supports || ["products"],
      },
    });
  };

  // Register all RaPay payment methods
  rapayGateways.forEach((gatewayId) => {
    try {
      registerRaPayGateway(gatewayId);
    } catch (e) {
      console.log("RaPay: Could not register " + gatewayId, e);
    }
  });
})();
