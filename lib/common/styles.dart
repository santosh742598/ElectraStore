import 'package:animations/animations.dart';
import 'package:flutter/material.dart';

import 'config/fonts.dart';
import 'constants/colors.dart';

const kProductTitleStyleLarge =
    TextStyle(fontSize: 18, fontWeight: FontWeight.bold);

const kTextField = InputDecoration(
  hintText: 'Enter your value',
  contentPadding: EdgeInsets.symmetric(vertical: 10.0, horizontal: 20.0),
  border: OutlineInputBorder(
    borderRadius: BorderRadius.all(Radius.circular(3.0)),
  ),
  enabledBorder: OutlineInputBorder(
    borderSide: BorderSide(color: Colors.blueGrey, width: 1.0),
    borderRadius: BorderRadius.all(Radius.circular(3.0)),
  ),
);

IconThemeData _customIconTheme(IconThemeData original) {
  return original.copyWith(color: kGrey900);
}

ThemeData buildLightTheme(String language) {
  // final ThemeData base = ThemeData.light();

  final base = ThemeData.light().copyWith(
    pageTransitionsTheme: const PageTransitionsTheme(
      builders: {
        TargetPlatform.android: FadeThroughPageTransitionsBuilder(),
        TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
      },
    ),
  );

  return base.copyWith(
    brightness: Brightness.light,
    colorScheme: kColorScheme,
    buttonColor: kTeal400,
    cardColor: Colors.white,
    // textSelectionColor: kTeal100,
    errorColor: kErrorRed,
    buttonTheme: const ButtonThemeData(
        colorScheme: kColorScheme,
        textTheme: ButtonTextTheme.normal,
        buttonColor: kDarkBG),
    primaryColorLight: kLightBG,
    primaryIconTheme: _customIconTheme(base.iconTheme),
    textTheme: _buildTextTheme(base.textTheme, language),
    primaryTextTheme: _buildTextTheme(base.primaryTextTheme, language),
    accentTextTheme: _buildTextTheme(base.accentTextTheme, language),
    iconTheme: _customIconTheme(base.iconTheme),
    hintColor: Colors.black26,
    backgroundColor: Colors.white,
    primaryColor: kLightPrimary,
    accentColor: kLightAccent,
    scaffoldBackgroundColor: kLightBG,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      textTheme: TextTheme(
        headline6: TextStyle(
          color: kDarkBG,
          fontSize: 18.0,
          fontWeight: FontWeight.w800,
        ),
      ),
      iconTheme: IconThemeData(
        color: kLightAccent,
      ),
    ),
    pageTransitionsTheme: const PageTransitionsTheme(builders: {
      TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
      TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
    }),
    tabBarTheme: const TabBarTheme(
      labelColor: Colors.black,
      unselectedLabelColor: Colors.black,
      labelPadding: EdgeInsets.zero,
      labelStyle: TextStyle(fontSize: 13),
      unselectedLabelStyle: TextStyle(fontSize: 13),
    ),
  );
}

TextTheme _buildTextTheme(TextTheme base, String language) {
  TextTheme newBase = kTextTheme(base, language);
  return newBase
      .copyWith(
        headline5: newBase.headline5
            .copyWith(fontWeight: FontWeight.w500, color: Colors.red),
        headline6: newBase.headline6.copyWith(fontSize: 18.0),
        caption: newBase.caption.copyWith(
          fontWeight: FontWeight.w400,
          fontSize: 14.0,
        ),
        subtitle1: newBase.subtitle1.copyWith(
          fontWeight: FontWeight.w400,
          fontSize: 16.0,
        ),
        button: newBase.button.copyWith(
          fontWeight: FontWeight.w400,
          fontSize: 14.0,
        ),
      )
      .apply(
        displayColor: kGrey900,
        bodyColor: kGrey900,
      )
      .copyWith(
        headline1: kHeadlineTheme(newBase).headline1.copyWith(),
        headline2: kHeadlineTheme(newBase).headline2.copyWith(),
        headline3: kHeadlineTheme(newBase).headline3.copyWith(),
        headline4: kHeadlineTheme(newBase).headline4.copyWith(),
        headline5: kHeadlineTheme(newBase).headline5.copyWith(),
        headline6: kHeadlineTheme(newBase).headline6.copyWith(),
      );
}

const ColorScheme kColorScheme = ColorScheme(
  primary: kTeal100,
  primaryVariant: kGrey900,
  secondary: kTeal50,
  secondaryVariant: kGrey900,
  surface: kSurfaceWhite,
  background: Colors.white,
  error: kErrorRed,
  onPrimary: kDarkBG,
  onSecondary: kGrey900,
  onSurface: kGrey900,
  onBackground: kGrey900,
  onError: kSurfaceWhite,
  brightness: Brightness.light,
);

ThemeData buildDarkTheme(String language) {
  final ThemeData base = ThemeData.dark();
  return base.copyWith(
    textTheme: _buildTextTheme(base.textTheme, language).apply(
      displayColor: kLightBG,
      bodyColor: kLightBG,
    ),
    primaryTextTheme: _buildTextTheme(base.primaryTextTheme, language).apply(
      displayColor: kLightBG,
      bodyColor: kLightBG,
    ),
    accentTextTheme: _buildTextTheme(base.accentTextTheme, language).apply(
      displayColor: kLightBG,
      bodyColor: kLightBG,
    ),
    canvasColor: kDarkBG,
    cardColor: kDarkBgLight,
    brightness: Brightness.dark,
    backgroundColor: kDarkBG,
    primaryColor: kDarkBG,
    primaryColorLight: kDarkBgLight,
    accentColor: kDarkAccent,
    scaffoldBackgroundColor: kDarkBG,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      textTheme: TextTheme(
        headline6: TextStyle(
          color: kDarkBG,
          fontSize: 18.0,
          fontWeight: FontWeight.w800,
        ),
      ),
      iconTheme: IconThemeData(
        color: kDarkAccent,
      ),
    ),
    buttonTheme: ButtonThemeData(
        colorScheme: kColorScheme.copyWith(onPrimary: kLightBG)),
    pageTransitionsTheme: const PageTransitionsTheme(builders: {
      TargetPlatform.iOS: FadeUpwardsPageTransitionsBuilder(),
      TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
    }),
    tabBarTheme: const TabBarTheme(
      labelColor: Colors.white,
      unselectedLabelColor: Colors.white,
      labelPadding: EdgeInsets.zero,
      labelStyle: TextStyle(fontSize: 13),
      unselectedLabelStyle: TextStyle(fontSize: 13),
    ),
  );
}

const kMessageContainerDecoration = BoxDecoration(
  border: Border(
    top: BorderSide(color: kTeal400, width: 2.0),
  ),
);

const kSendButtonTextStyle = TextStyle(
  color: kTeal400,
  fontWeight: FontWeight.bold,
  fontSize: 18.0,
);

const kMessageTextFieldDecoration = InputDecoration(
  contentPadding: EdgeInsets.symmetric(vertical: 10.0, horizontal: 20.0),
  hintText: 'Type your message here...',
  border: InputBorder.none,
);
