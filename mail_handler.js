async function sendFormToMail(formData) {
    const payload = {
    form_type: formData.form_type || "Website Form",

    name: formData.name || "",

    email: formData.email || "",

    mobile: formData.mobile || "",

    dob: formData.dob || "",

    couponCode: formData.couponCode || "",

    signupDate: formData.signupDate || "",

    visit_date: formData.visit_date || "",

    guests: formData.guests || "",

    event_type: formData.event_type || "",

    message: formData.message || ""
};

    try {
        const mailEndpoint =
    formData.form_type === "Winery Visit Reservation"
        ? "visit_mail.php"
        : "send_gift_mail.php";

const response = await fetch(mailEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        const responseText = await response.text();

console.log("PHP HTTP Status:", response.status);
console.log("PHP Raw Response:", responseText);

let result;

try {
    result = JSON.parse(responseText);
} catch (parseError) {
    throw new Error(
        "PHP returned invalid response: " + responseText
    );
}

console.log("Signup email response:", result);

if (
    !response.ok ||
    (result.status !== "success" && result.success !== true)
) {
    throw new Error(
        result.message ||
        result.error ||
        "Email could not be sent."
    );
}

return result;

    } catch (error) {
    console.error("Signup email error:", error);
    alert("Server Error: " + error.message);
    throw error;
}
}